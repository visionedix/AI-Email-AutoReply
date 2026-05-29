<?php

namespace Tests\Unit;

use App\Services\Mail\EmailProviderManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GmailEmailProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Config::set('mailbox.provider', 'gmail');
        Config::set('mailbox.timeout', 15);
        Config::set('mailbox.gmail.client_id', 'client-id');
        Config::set('mailbox.gmail.client_secret', 'client-secret');
        Config::set('mailbox.gmail.refresh_token', 'refresh-token');
        Config::set('mailbox.gmail.user_id', 'me');
        Config::set('mailbox.gmail.token_url', 'https://oauth2.googleapis.com/token');
        Config::set('mailbox.gmail.api_url', 'https://gmail.googleapis.com/gmail/v1');
    }

    public function test_it_reads_gmail_inbox_messages(): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token'], 200),
            'gmail.googleapis.com/*' => Http::response([
                'messages' => [
                    ['id' => 'message-id', 'threadId' => 'thread-id'],
                ],
            ], 200),
        ]);

        $messages = app(EmailProviderManager::class)->inbox(5);

        $this->assertSame('message-id', $messages['messages'][0]['id']);

        $gmailRequest = Http::recorded(fn ($request): bool => str_contains($request->url(), '/users/me/messages'))
            ->last()[0] ?? null;

        $this->assertNotNull($gmailRequest);
        $this->assertStringContainsString('/users/me/messages', $gmailRequest->url());
        $this->assertStringContainsString('maxResults=5', $gmailRequest->url());
        $this->assertStringContainsString('labelIds=INBOX', $gmailRequest->url());
    }

    public function test_it_filters_gmail_unread_messages(): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token'], 200),
            'gmail.googleapis.com/*' => Http::response([
                'messages' => [
                    ['id' => 'message-id', 'threadId' => 'thread-id'],
                ],
            ], 200),
        ]);

        app(EmailProviderManager::class)->inbox(10, true);

        $gmailRequest = Http::recorded(fn ($request): bool => str_contains($request->url(), '/users/me/messages'))
            ->last()[0] ?? null;

        $this->assertNotNull($gmailRequest);
        $this->assertStringContainsString('q=in%3Ainbox%20is%3Aunread', $gmailRequest->url());
        $this->assertStringContainsString('labelIds=INBOX', $gmailRequest->url());
    }

    public function test_it_sends_gmail_message(): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token'], 200),
            'gmail.googleapis.com/*' => Http::response(['id' => 'sent-id'], 200),
        ]);

        app(EmailProviderManager::class)->send([
            'to' => ['customer@example.com'],
            'subject' => 'Reply',
            'body' => '<p>Thanks</p>',
            'content_type' => 'HTML',
        ]);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/messages/send')
            && filled(data_get($request->data(), 'raw')));
    }
}
