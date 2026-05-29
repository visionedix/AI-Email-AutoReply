<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MailViewerTest extends TestCase
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

    public function test_it_renders_a_message_view_page(): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token'], 200),
            'gmail.googleapis.com/*' => Http::response([
                'id' => 'message-id',
                'threadId' => 'thread-id',
                'labelIds' => ['INBOX'],
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'Hello there'],
                        ['name' => 'From', 'value' => 'sender@example.com'],
                        ['name' => 'To', 'value' => 'me@example.com'],
                        ['name' => 'Date', 'value' => 'Wed, 29 May 2026 10:00:00 +0000'],
                    ],
                    'body' => [
                        'data' => rtrim(strtr(base64_encode('<p>Body</p>'), '+/', '-_'), '='),
                    ],
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

        $response = $this->get('/admin/mail/messages/message-id');

        $response->assertOk();
        $response->assertSee('Hello there');
        $response->assertSee('sender@example.com');
        $response->assertSee('Body');
    }
}
