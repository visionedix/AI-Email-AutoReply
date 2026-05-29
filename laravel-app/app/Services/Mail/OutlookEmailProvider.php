<?php

namespace App\Services\Mail;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OutlookEmailProvider implements EmailProvider
{
    public function inbox(int $limit = 10, bool $unreadOnly = false): array
    {
        $limit = max(1, min($limit, 50));

        $query = [
            '$top' => $limit,
            '$orderby' => 'receivedDateTime desc',
            '$select' => 'id,subject,from,receivedDateTime,isRead,bodyPreview,importance,hasAttachments',
        ];

        if ($unreadOnly) {
            $query['$filter'] = 'isRead eq false';
        }

        return $this->graph()
            ->get($this->mailboxUrl('/mailFolders/inbox/messages'), $query)
            ->throw()
            ->json();
    }

    public function message(string $messageId): array
    {
        return $this->graph()
            ->get($this->mailboxUrl('/messages/'.rawurlencode($messageId)), [
                '$select' => 'id,subject,from,toRecipients,ccRecipients,receivedDateTime,isRead,body,bodyPreview,importance,hasAttachments',
            ])
            ->throw()
            ->json();
    }

    public function markAsRead(string $messageId): array
    {
        return $this->graph()
            ->patch($this->mailboxUrl('/messages/'.rawurlencode($messageId)), [
                'isRead' => true,
            ])
            ->throw()
            ->json();
    }

    public function send(array $payload): void
    {
        $message = [
            'subject' => $payload['subject'],
            'body' => [
                'contentType' => $payload['content_type'] ?? 'HTML',
                'content' => $payload['body'],
            ],
            'toRecipients' => $this->recipients($payload['to']),
        ];

        foreach (['cc' => 'ccRecipients', 'bcc' => 'bccRecipients'] as $input => $graphKey) {
            if (! empty($payload[$input])) {
                $message[$graphKey] = $this->recipients($payload[$input]);
            }
        }

        $this->graph()
            ->post($this->mailboxUrl('/sendMail'), [
                'message' => $message,
                'saveToSentItems' => $payload['save_to_sent_items'] ?? true,
            ])
            ->throw();
    }

    private function graph(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->timeout(config('mailbox.timeout'));
    }

    private function accessToken(): string
    {
        $this->ensureConfigured(['tenant_id', 'client_id', 'client_secret']);

        return Cache::remember('mailbox.outlook.access_token', now()->addMinutes(50), function (): string {
            $response = Http::asForm()
                ->timeout(config('mailbox.timeout'))
                ->post(rtrim(config('mailbox.outlook.token_url'), '/').'/'.config('mailbox.outlook.tenant_id').'/oauth2/v2.0/token', [
                    'client_id' => config('mailbox.outlook.client_id'),
                    'client_secret' => config('mailbox.outlook.client_secret'),
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ])
                ->throw()
                ->json();

            return $response['access_token'] ?? throw new RuntimeException('Microsoft Graph did not return an access token.');
        });
    }

    private function mailboxUrl(string $path): string
    {
        $this->ensureConfigured(['mailbox']);

        return rtrim(config('mailbox.outlook.graph_url'), '/')
            .'/users/'
            .rawurlencode(config('mailbox.outlook.mailbox'))
            .Str::start($path, '/');
    }

    private function recipients(array|string $addresses): array
    {
        $addresses = is_array($addresses) ? $addresses : [$addresses];

        return collect($addresses)
            ->filter()
            ->map(fn (string $address): array => [
                'emailAddress' => ['address' => $address],
            ])
            ->values()
            ->all();
    }

    private function ensureConfigured(array $keys): void
    {
        foreach ($keys as $key) {
            if (blank(config("mailbox.outlook.$key"))) {
                throw new RuntimeException("Missing Outlook configuration value: mailbox.outlook.$key");
            }
        }
    }
}
