<?php

namespace Tests\Feature;

use App\Admin\Models\EmailTemplate;
use App\Admin\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuotationGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Config::set('ai.driver', 'auto');
        Config::set('ai.timeout', 15);
        Config::set('ai.openai.api_key', 'test-openai-key');
        Config::set('ai.openai.base_url', 'https://api.openai.com/v1');
        Config::set('ai.openai.model', 'chat-latest');
        Config::set('mailbox.provider', 'gmail');
        Config::set('mailbox.timeout', 15);
        Config::set('mailbox.gmail.client_id', 'client-id');
        Config::set('mailbox.gmail.client_secret', 'client-secret');
        Config::set('mailbox.gmail.refresh_token', 'refresh-token');
        Config::set('mailbox.gmail.user_id', 'me');
        Config::set('mailbox.gmail.token_url', 'https://oauth2.googleapis.com/token');
        Config::set('mailbox.gmail.api_url', 'https://gmail.googleapis.com/gmail/v1');
    }

    public function test_it_generates_a_quotation_and_creates_a_gmail_draft(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('quotations/sample.pdf', 'pdf-content');

        $product = Product::create([
            'product_name' => 'SKF Bearing',
            'sku' => 'SKF-001',
            'unit' => 'pcs',
            'per_unit_price' => 120.00,
            'keyword_search' => 'bearing, ball bearing, skf bearing',
            'quotation_documents' => 'quotations/sample.pdf',
        ]);

        $template = EmailTemplate::create([
            'product_id' => (string) $product->id,
            'template_name' => 'SKF Quotation',
            'template_subject' => 'Quotation for {product_name}',
            'template_body' => 'Dear {customer_name}, please find quotation for {product_name}.',
        ]);

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token'], 200),
            'gmail.googleapis.com/*/messages/message-1' => Http::response([
                'id' => 'message-1',
                'threadId' => 'thread-1',
                'labelIds' => ['INBOX'],
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'Need bearing quotation'],
                        ['name' => 'From', 'value' => 'Buyer Name <buyer@example.com>'],
                        ['name' => 'To', 'value' => 'sales@example.com'],
                        ['name' => 'Date', 'value' => 'Wed, 29 May 2026 10:00:00 +0000'],
                    ],
                    'body' => [
                        'data' => rtrim(strtr(base64_encode('<p>Please send a bearing quotation. This is an inquiry.</p>'), '+/', '-_'), '='),
                    ],
                ],
            ], 200),
            'api.openai.com/v1/responses' => Http::sequence()
                ->push([
                    'output' => [
                        [
                            'type' => 'message',
                            'content' => [
                                [
                                    'type' => 'output_text',
                                    'text' => json_encode([
                                        'selected_product_id' => 1,
                                        'matched_keyword' => 'bearing',
                                        'confidence' => 0.97,
                                        'reason' => 'The email is clearly requesting the SKF Bearing product.',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'output' => [
                        [
                            'type' => 'message',
                            'content' => [
                                [
                                    'type' => 'output_text',
                                    'text' => json_encode([
                                        'subject' => 'Quotation for SKF Bearing',
                                        'body' => 'Dear Buyer Name, thank you for your inquiry. Please find the quotation for SKF Bearing attached.',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ], 200),
            'gmail.googleapis.com/*/drafts' => Http::response([
                'id' => 'draft-1',
                'message' => [
                    'id' => 'draft-message-1',
                ],
            ], 200),
        ]);

        $user = new User();
        $user->forceFill([
            'id' => 1,
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
        ]);

        $this->actingAs($user);

        $response = $this->post('/admin/quotations', [
            'message_id' => 'message-1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quotations', [
            'message_id' => 'message-1',
            'gmail_message_id' => 'message-1',
            'product_id' => $product->id,
            'template_id' => (string) $template->id,
            'gmail_draft_id' => 'draft-1',
            'customer_email' => 'buyer@example.com',
            'subject' => 'Quotation for SKF Bearing',
            'body' => 'Dear Buyer Name, thank you for your inquiry. Please find the quotation for SKF Bearing attached.',
            'status' => 'draft',
        ]);
    }
}
