<?php

namespace Tests\Unit;

use App\Services\Mail\EmailProviderManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutlookMailServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        Config::set('mailbox.provider', 'outlook');
        Config::set('mailbox.timeout', 15);
        Config::set('mailbox.outlook.tenant_id', 'tenant-id');
        Config::set('mailbox.outlook.client_id', 'client-id');
        Config::set('mailbox.outlook.client_secret', 'client-secret');
        Config::set('mailbox.outlook.mailbox', 'mailbox@example.com');
        Config::set('mailbox.outlook.token_url', 'https://login.microsoftonline.com');
        Config::set('mailbox.outlook.graph_url', 'https://graph.microsoft.com/v1.0');
    }

    public function test_it_reads_inbox_messages_from_graph(): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'token'], 200),
            'graph.microsoft.com/*' => Http::response([
                'value' => [
                    ['id' => 'message-id', 'subject' => 'Hello'],
                ],
            ], 200),
        ]);

        $messages = app(EmailProviderManager::class)->inbox(5);

        $this->assertSame('Hello', $messages['value'][0]['subject']);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/mailFolders/inbox/messages')
            && $request['$top'] === 5
            && $request->hasHeader('Authorization', 'Bearer token'));
    }

    public function test_it_filters_outlook_unread_messages(): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'token'], 200),
            'graph.microsoft.com/*' => Http::response([
                'value' => [
                    ['id' => 'message-id', 'subject' => 'Hello'],
                ],
            ], 200),
        ]);

        app(EmailProviderManager::class)->inbox(10, true);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/mailFolders/inbox/messages')
            && $request['$filter'] === 'isRead eq false'
            && $request->hasHeader('Authorization', 'Bearer token'));
    }

    public function test_it_sends_mail_through_graph(): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'token'], 200),
            'graph.microsoft.com/*' => Http::response(null, 202),
        ]);

        app(EmailProviderManager::class)->send([
            'to' => ['customer@example.com'],
            'subject' => 'Reply',
            'body' => '<p>Thanks</p>',
            'content_type' => 'HTML',
        ]);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/sendMail')
            && $request['message']['subject'] === 'Reply'
            && $request['message']['toRecipients'][0]['emailAddress']['address'] === 'customer@example.com'
            && $request['saveToSentItems'] === true);
    }
}
