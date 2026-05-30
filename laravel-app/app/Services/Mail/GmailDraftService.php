<?php

namespace App\Services\Mail;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GmailDraftService
{
    public function createQuotationDraft(string $subject, string $body, string $attachmentPath, ?string $recipientEmail = null): array
    {
        $raw = $this->buildRawMessage($subject, $body, $attachmentPath, $recipientEmail);

        $response = $this->gmail()
            ->post($this->userUrl('/drafts'), [
                'message' => [
                    'raw' => $raw,
                ],
            ])
            ->throw()
            ->json();

        return [
            'draft_id' => data_get($response, 'id'),
            'message_id' => data_get($response, 'message.id'),
        ];
    }

    public function updateQuotationDraft(string $draftId, string $subject, string $body, string $attachmentPath, ?string $recipientEmail = null): array
    {
        $raw = $this->buildRawMessage($subject, $body, $attachmentPath, $recipientEmail);

        $response = $this->gmail()
            ->put($this->userUrl('/drafts/'.rawurlencode($draftId)), [
                'id' => $draftId,
                'message' => [
                    'raw' => $raw,
                ],
            ])
            ->throw()
            ->json();

        return [
            'draft_id' => data_get($response, 'id', $draftId),
            'message_id' => data_get($response, 'message.id'),
        ];
    }

    public function deleteQuotationDraft(string $draftId): void
    {
        $this->gmail()
            ->delete($this->userUrl('/drafts/'.rawurlencode($draftId)))
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

    private function buildRawMessage(string $subject, string $body, string $attachmentPath, ?string $recipientEmail = null): string
    {
        $this->validateAttachment($attachmentPath);

        $boundary = 'quotation_'.Str::random(24);
        $isHtml = Str::contains(Str::lower($body), ['<html', '<body', '<div', '<p', '<br', '<span', '<table']);
        $bodyMimeType = $isHtml ? 'text/html' : 'text/plain';
        $attachmentAbsolutePath = Storage::disk('public')->path($attachmentPath);
        $attachmentFileName = basename($attachmentPath);
        $attachmentMimeType = $this->attachmentMimeType($attachmentPath);
        $attachmentContent = chunk_split(base64_encode((string) file_get_contents($attachmentAbsolutePath)), 76, "\r\n");

        $parts = [];
        $parts[] = "--{$boundary}\r\n";
        $parts[] = "Content-Type: {$bodyMimeType}; charset=UTF-8\r\n";
        $parts[] = "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $parts[] = $body."\r\n";

        $parts[] = "--{$boundary}\r\n";
        $parts[] = "Content-Type: {$attachmentMimeType}; name=\"{$attachmentFileName}\"\r\n";
        $parts[] = "Content-Disposition: attachment; filename=\"{$attachmentFileName}\"\r\n";
        $parts[] = "Content-Transfer-Encoding: base64\r\n\r\n";
        $parts[] = $attachmentContent."\r\n";
        $parts[] = "--{$boundary}--";

        $headers = [
            'Subject: '.$subject,
            'MIME-Version: 1.0',
            'Content-Type: multipart/mixed; boundary="'.$boundary.'"',
        ];

        if (filled($recipientEmail)) {
            array_unshift($headers, 'To: '.$recipientEmail);
        }

        $message = implode("\r\n", $headers)."\r\n\r\n".implode('', $parts);

        return rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
    }

    private function validateAttachment(string $attachmentPath): void
    {
        if (blank($attachmentPath) || ! Storage::disk('public')->exists($attachmentPath)) {
            throw new RuntimeException('Quotation attachment file does not exist.');
        }

        $extension = strtolower(pathinfo($attachmentPath, PATHINFO_EXTENSION));

        if (! in_array($extension, ['pdf', 'doc', 'docx', 'xlsx'], true)) {
            throw new RuntimeException('Quotation attachments must be PDF, DOC, DOCX, or XLSX files.');
        }
    }

    private function attachmentMimeType(string $attachmentPath): string
    {
        return match (strtolower(pathinfo($attachmentPath, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
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
