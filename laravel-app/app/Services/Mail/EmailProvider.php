<?php

namespace App\Services\Mail;

interface EmailProvider
{
    public function inbox(int $limit = 10, bool $unreadOnly = false): array;

    public function message(string $messageId): array;

    public function markAsRead(string $messageId): array;

    public function send(array $payload): void;
}
