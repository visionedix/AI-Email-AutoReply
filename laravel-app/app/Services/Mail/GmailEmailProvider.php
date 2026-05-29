<?php

namespace App\Services\Mail;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GmailEmailProvider implements EmailProvider
{
    public function inbox(int $limit = 10, bool $unreadOnly = false): array
    {
        $limit = max(1, min($limit, 50));

        $query = [
            'maxResults' => $limit,
            'labelIds' => 'INBOX',
        ];

        if ($unreadOnly) {
            $query['q'] = 'in:inbox is:unread';
        }

        return $this->gmail()
            ->get($this->userUrl('/messages'), $query)
            ->throw()
            ->json();
    }

    public function message(string $messageId): array
    {
        return $this->gmail()
            ->get($this->userUrl('/messages/'.rawurlencode($messageId)), [
                'format' => 'full',
            ])
            ->throw()
            ->json();
    }

    public function markAsRead(string $messageId): array
    {
        return $this->gmail()
            ->post($this->userUrl('/messages/'.rawurlencode($messageId).'/modify'), [
                'removeLabelIds' => ['UNREAD'],
            ])
            ->throw()
            ->json();
    }

    public function send(array $payload): void
    {
        $this->gmail()
            ->post($this->userUrl('/messages/send'), [
                'raw' => $this->rawMessage($payload),
            ])
            ->throw();
    }

    private function gmail(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->timeout(config('mailbox.timeout'));
    }

    private function accessToken(): string
    {
        if (filled(config('mailbox.gmail.access_token'))) {
            return config('mailbox.gmail.access_token');
        }

        $this->ensureConfigured(['client_id', 'client_secret', 'refresh_token']);

        try {
            return Cache::remember('mailbox.gmail.access_token', now()->addMinutes(50), function (): string {
                $response = Http::asForm()
                    ->timeout(config('mailbox.timeout'))
                    ->post(config('mailbox.gmail.token_url'), [
                        'client_id' => config('mailbox.gmail.client_id'),
                        'client_secret' => config('mailbox.gmail.client_secret'),
                        'refresh_token' => config('mailbox.gmail.refresh_token'),
                        'grant_type' => 'refresh_token',
                    ])
                    ->throw()
                    ->json();

                return $response['access_token'] ?? throw new RuntimeException('Gmail did not return an access token.');
            });
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Gmail is missing a usable OAuth token. Set GMAIL_REFRESH_TOKEN for normal use, or GMAIL_ACCESS_TOKEN for a temporary manual token.',
                previous: $exception
            );
        }
    }

    private function userUrl(string $path): string
    {
        return rtrim(config('mailbox.gmail.api_url'), '/')
            .'/users/'
            .rawurlencode(config('mailbox.gmail.user_id'))
            .$path;
    }

    private function rawMessage(array $payload): string
    {
        $headers = [
            'To' => implode(', ', $payload['to']),
            'Subject' => $payload['subject'],
            'MIME-Version' => '1.0',
            'Content-Type' => ($payload['content_type'] ?? 'HTML') === 'HTML'
                ? 'text/html; charset=UTF-8'
                : 'text/plain; charset=UTF-8',
        ];

        foreach (['cc' => 'Cc', 'bcc' => 'Bcc'] as $input => $header) {
            if (! empty($payload[$input])) {
                $headers[$header] = implode(', ', $payload[$input]);
            }
        }

        $message = collect($headers)
            ->map(fn (string $value, string $key): string => "$key: $value")
            ->implode("\r\n")
            ."\r\n\r\n"
            .$payload['body'];

        return rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
    }

    private function ensureConfigured(array $keys): void
    {
        foreach ($keys as $key) {
            if (blank(config("mailbox.gmail.$key"))) {
                throw new RuntimeException(
                    'Missing Gmail configuration value: mailbox.gmail.'.$key.'. '
                    .'Use GMAIL_REFRESH_TOKEN for normal use, or set GMAIL_ACCESS_TOKEN for a temporary token.'
                );
            }
        }
    }
}
