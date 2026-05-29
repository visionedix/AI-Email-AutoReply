<?php

namespace App\Services\Mail;

use InvalidArgumentException;
use RuntimeException;

class EmailProviderManager implements EmailProvider
{
    public function __construct(
        private readonly OutlookEmailProvider $outlook,
        private readonly GmailEmailProvider $gmail,
    ) {
    }

    public function inbox(int $limit = 10, bool $unreadOnly = false): array
    {
        return $this->provider()->inbox($limit, $unreadOnly);
    }

    public function message(string $messageId): array
    {
        return $this->provider()->message($messageId);
    }

    public function markAsRead(string $messageId): array
    {
        return $this->provider()->markAsRead($messageId);
    }

    public function send(array $payload): void
    {
        $this->provider()->send($payload);
    }

    private function provider(): EmailProvider
    {
        return match (config('mailbox.provider')) {
            'outlook' => $this->outlook,
            'gmail' => $this->gmail,
            'auto' => $this->autoProvider(),
            default => throw new InvalidArgumentException('MAIL_PROVIDER must be auto, outlook, or gmail.'),
        };
    }

    private function autoProvider(): EmailProvider
    {
        if ($this->hasGmailCredentials()) {
            return $this->gmail;
        }

        if ($this->hasOutlookCredentials()) {
            return $this->outlook;
        }

        throw new RuntimeException(
            'No email provider is configured. Set MAIL_PROVIDER and the matching Gmail or Outlook credentials in .env.'
        );
    }

    private function hasGmailCredentials(): bool
    {
        return filled(config('mailbox.gmail.client_id'))
            && filled(config('mailbox.gmail.client_secret'))
            && filled(config('mailbox.gmail.refresh_token'));
    }

    private function hasOutlookCredentials(): bool
    {
        return filled(config('mailbox.outlook.tenant_id'))
            && filled(config('mailbox.outlook.client_id'))
            && filled(config('mailbox.outlook.client_secret'))
            && filled(config('mailbox.outlook.mailbox'));
    }
}
