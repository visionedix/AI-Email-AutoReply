<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\Mail\EmailProviderManager;
use App\Services\ProductKeywordMatcher;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use RuntimeException;

class MailController extends Controller
{
    public function __construct(
        private readonly EmailProviderManager $mail,
        private readonly ProductKeywordMatcher $matcher,
    ) {
    }

    public function inbox(Request $request)
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'unread_only' => ['sometimes', 'boolean'],
        ]);

        $messages = $this->normalizeInboxMessages(
            $this->mail->inbox(
                $validated['limit'] ?? 10,
                $request->boolean('unread_only')
            )
        );

        return response()->view('admin.mail.index', [
            'messages' => $messages,
            'filters' => [
                'limit' => $validated['limit'] ?? 10,
                'unread_only' => $request->boolean('unread_only'),
            ],
        ]);
    }

    public function show(string $messageId)
    {
        $message = $this->normalizeMessage($this->mail->message($messageId));
        $searchContent = trim($message['subject'].' '.strip_tags($message['body']));
        $matchedProducts = $this->matcher->matchProducts($searchContent);
        $quotation = Quotation::query()
            ->where('message_id', $messageId)
            ->orWhere('gmail_message_id', $messageId)
            ->latest('id')
            ->first();

        return response()->view('admin.mail.show', [
            'message' => $message,
            'matchedProducts' => $matchedProducts,
            'quotation' => $quotation,
        ]);
    }

    public function markAsRead(string $messageId): JsonResponse
    {
        if (request()->expectsJson()) {
            return $this->respond(fn () => $this->mail->markAsRead($messageId));
        }

        $this->mail->markAsRead($messageId);

        return redirect()->back()->with('success', 'Message marked as read.');
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'array', 'min:1'],
            'to.*' => ['required', 'email'],
            'cc' => ['sometimes', 'array'],
            'cc.*' => ['required', 'email'],
            'bcc' => ['sometimes', 'array'],
            'bcc.*' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'content_type' => ['sometimes', 'in:Text,HTML'],
            'save_to_sent_items' => ['sometimes', 'boolean'],
        ]);

        return $this->respond(function () use ($validated): array {
            $this->mail->send($validated);

            return [
                'message' => 'Email accepted by '.config('mailbox.provider').'.',
            ];
        }, 202);
    }

    private function respond(callable $callback, int $successStatus = 200): JsonResponse
    {
        try {
            return response()->json($callback(), $successStatus);
        } catch (RequestException $exception) {
            return response()->json([
                'message' => 'Email provider request failed.',
                'error' => $exception->response->json('error.message')
                    ?? $exception->response->json('error')
                    ?? $exception->getMessage(),
            ], $exception->response->status());
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>
     */
    private function normalizeMessage(array $message): array
    {
        $provider = config('mailbox.provider');

        if ($provider === 'gmail' || ($provider === 'auto' && isset($message['threadId']))) {
            return [
                'id' => $message['id'] ?? null,
                'subject' => $this->gmailHeaderValue($message, 'Subject') ?? '(no subject)',
                'from' => $this->gmailHeaderValue($message, 'From'),
                'to' => $this->gmailHeaderValue($message, 'To'),
                'cc' => $this->gmailHeaderValue($message, 'Cc'),
                'date' => $this->gmailHeaderValue($message, 'Date'),
                'isRead' => ! in_array('UNREAD', $message['labelIds'] ?? [], true),
                'preview' => $message['snippet'] ?? '',
                'body' => $this->gmailBody($message),
                'raw' => $message,
            ];
        }

        return [
            'id' => $message['id'] ?? null,
            'subject' => $message['subject'] ?? '(no subject)',
            'from' => $this->mailboxAddress($message, 'from'),
            'to' => $this->recipientsToString($message['toRecipients'] ?? []),
            'cc' => $this->recipientsToString($message['ccRecipients'] ?? []),
            'date' => $message['receivedDateTime'] ?? null,
            'isRead' => (bool) ($message['isRead'] ?? false),
            'preview' => (string) Arr::get($message, 'bodyPreview', ''),
            'body' => $this->outlookBody($message),
            'raw' => $message,
        ];
    }

    /**
     * @param  array<string, mixed>  $inbox
     * @return array<int, array<string, mixed>>
     */
    private function normalizeInboxMessages(array $inbox): array
    {
        $provider = config('mailbox.provider');
        $limit = (int) request()->integer('limit', 10);

        if ($provider === 'gmail' || ($provider === 'auto' && isset($inbox['messages']))) {
            return collect($inbox['messages'] ?? [])
                ->take($limit)
                ->map(fn (array $item): array => $this->normalizeMessage($this->mail->message($item['id'])))
                ->values()
                ->all();
        }

        return collect($inbox['value'] ?? [])
            ->map(fn (array $message): array => $this->normalizeMessage($message))
            ->values()
            ->all();
    }

    private function gmailHeaderValue(array $message, string $name): ?string
    {
        $headers = Arr::get($message, 'payload.headers', []);

        foreach ($headers as $header) {
            if (($header['name'] ?? null) === $name) {
                return $header['value'] ?? null;
            }
        }

        return null;
    }

    private function gmailBody(array $message): string
    {
        $parts = Arr::get($message, 'payload.parts', []);

        foreach ($parts as $part) {
            $mimeType = $part['mimeType'] ?? null;
            $body = $part['body']['data'] ?? null;

            if (! $body) {
                continue;
            }

            if (in_array($mimeType, ['text/html', 'text/plain'], true)) {
                return $this->decodeGmailBody($body);
            }
        }

        $body = Arr::get($message, 'payload.body.data', '');

        return $body ? $this->decodeGmailBody($body) : '';
    }

    private function decodeGmailBody(string $body): string
    {
        return base64_decode(strtr($body, '-_', '+/')) ?: '';
    }

    private function outlookBody(array $message): string
    {
        $body = (string) Arr::get($message, 'body.content', '');

        return $body;
    }

    private function mailboxAddress(array $message, string $key): ?string
    {
        return Arr::get($message, "{$key}.emailAddress.address");
    }

    private function recipientsToString(array $recipients): string
    {
        return collect($recipients)
            ->map(fn (array $recipient): ?string => Arr::get($recipient, 'emailAddress.address'))
            ->filter()
            ->implode(', ');
    }
}
