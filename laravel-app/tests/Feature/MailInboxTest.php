<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MailInboxTest extends TestCase
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

    public function test_it_renders_an_email_inbox_page(): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'token'], 200),
            'gmail.googleapis.com/*messages?maxResults=10*' => Http::response([
                'messages' => [
                    ['id' => 'message-1'],
                    ['id' => 'message-2'],
                ],
            ], 200),
            'gmail.googleapis.com/*messages/message-1*' => Http::response([
                'id' => 'message-1',
                'threadId' => 'thread-1',
                'labelIds' => ['INBOX', 'UNREAD'],
                'snippet' => 'First preview',
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'First message'],
                        ['name' => 'From', 'value' => 'alice@example.com'],
                        ['name' => 'Date', 'value' => 'Wed, 29 May 2026 10:00:00 +0000'],
                    ],
                    'body' => [
                        'data' => rtrim(strtr(base64_encode('<p>First body</p>'), '+/', '-_'), '='),
                    ],
                ],
            ], 200),
            'gmail.googleapis.com/*messages/message-2*' => Http::response([
                'id' => 'message-2',
                'threadId' => 'thread-2',
                'labelIds' => ['INBOX'],
                'snippet' => 'Second preview',
                'payload' => [
                    'headers' => [
                        ['name' => 'Subject', 'value' => 'Second message'],
                        ['name' => 'From', 'value' => 'bob@example.com'],
                        ['name' => 'Date', 'value' => 'Wed, 29 May 2026 11:00:00 +0000'],
                    ],
                    'body' => [
                        'data' => rtrim(strtr(base64_encode('<p>Second body</p>'), '+/', '-_'), '='),
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

        $response = $this->get('/admin/mail/messages');

        $response->assertOk();
        $response->assertSee('Email Inbox');
        $response->assertSee('First message');
        $response->assertSee('Second message');
        $response->assertSee('alice@example.com');
        $response->assertSee('Unread');
    }
}
