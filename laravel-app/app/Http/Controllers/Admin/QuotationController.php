<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuotationStoreRequest;
use App\Http\Requests\Admin\QuotationUpdateRequest;
use App\Jobs\CreateQuotationDraftJob;
use App\Models\EmailTemplate;
use App\Models\Quotation;
use App\Services\Mail\EmailProviderManager;
use App\Services\Mail\GmailDraftService;
use App\Services\ProductKeywordMatcher;
use App\Services\QuotationGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class QuotationController extends Controller
{
    public function __construct(
        private readonly EmailProviderManager $mail,
        private readonly ProductKeywordMatcher $matcher,
        private readonly QuotationGeneratorService $generator,
        private readonly GmailDraftService $drafts,
    ) {
    }

    public function index()
    {
        $this->authorize('viewAny', Quotation::class);

        $quotations = Quotation::query()
            ->with(['product', 'template'])
            ->latest('id')
            ->paginate(15);

        return view('admin.quotations.index', compact('quotations'));
    }

    public function store(QuotationStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Quotation::class);

        $messageId = $request->validated()['message_id'];

        if ($quotation = Quotation::query()
            ->where('message_id', $messageId)
            ->orWhere('gmail_message_id', $messageId)
            ->latest('id')
            ->first()
        ) {
            return redirect()
                ->to('/admin/quotations/'.$quotation->id)
                ->with('success', 'Quotation already exists for this email.');
        }

        try {
            $message = $this->normalizeMessage($this->mail->message($messageId));
        } catch (Throwable $throwable) {
            return back()->withErrors([
                'message_id' => 'Unable to load the original email: '.$throwable->getMessage(),
            ]);
        }

        $searchContent = trim($message['subject'].' '.strip_tags($message['body']));
        $matches = $this->matcher->matchProducts($searchContent);

        if ($matches->isEmpty()) {
            return back()->withErrors([
                'message_id' => 'No matching product keywords were found for this email.',
            ]);
        }

        [$product, $template, $matchedKeyword] = $this->resolveQuotationTarget($matches);

        if (! $product || ! $template) {
            return back()->withErrors([
                'message_id' => 'A matching product was found, but no email template exists for it.',
            ]);
        }

        $generated = $this->generator->generate($product, $template, $message);
        $customerEmail = $this->customerEmail($message);
        $quotation = Quotation::create([
            'message_id' => $messageId,
            'gmail_message_id' => $messageId,
            'product_id' => $product->id,
            'template_id' => (string) $template->id,
            'customer_email' => $customerEmail ?: null,
            'subject' => $generated['subject'],
            'body' => $generated['body'],
            'attachment' => $product->quotation_documents,
            'status' => 'draft',
        ]);

        try {
            CreateQuotationDraftJob::dispatchSync($quotation->id);
        } catch (Throwable $throwable) {
            return redirect()
                ->to('/admin/quotations/'.$quotation->id)
                ->withErrors([
                    'quotation' => 'Quotation was saved, but Gmail draft creation failed: '.$throwable->getMessage(),
                ]);
        }

        return redirect()
            ->to('/admin/quotations/'.$quotation->id)
            ->with('success', 'Quotation generated successfully.'.($matchedKeyword ? ' Matched keyword: '.$matchedKeyword : ''));
    }

    public function show(Quotation $quotation)
    {
        $this->authorize('view', $quotation);

        $quotation->load(['product', 'template']);

        $originalMessage = null;

        try {
            $originalMessage = $this->normalizeMessage($this->mail->message($quotation->gmail_message_id));
        } catch (Throwable) {
            $originalMessage = null;
        }

        return view('admin.quotations.show', [
            'quotation' => $quotation,
            'originalMessage' => $originalMessage,
        ]);
    }

    public function edit(Quotation $quotation)
    {
        $this->authorize('update', $quotation);

        return view('admin.quotations.edit', compact('quotation'));
    }

    public function update(QuotationUpdateRequest $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            if ($this->isLocalGeneratedAttachment($quotation->attachment)) {
                Storage::disk('public')->delete($quotation->attachment);
            }

            $data['attachment'] = $request->file('attachment')->store('quotations/attachments', 'public');
        }

        $quotation->update([
            'subject' => $data['subject'],
            'body' => $data['body'],
            'attachment' => $data['attachment'] ?? $quotation->attachment,
            'status' => 'draft',
        ]);

        $quotation->refresh();

        try {
            CreateQuotationDraftJob::dispatchSync($quotation->id);
        } catch (Throwable $throwable) {
            return redirect()
                ->to('/admin/quotations/'.$quotation->id)
                ->withErrors([
                    'quotation' => 'Quotation draft was updated locally, but Gmail draft sync failed: '.$throwable->getMessage(),
                ]);
        }

        return redirect()
            ->to('/admin/quotations/'.$quotation->id)
            ->with('success', 'Quotation draft updated successfully.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);

        if (filled($quotation->gmail_draft_id)) {
            try {
                $this->drafts->deleteQuotationDraft($quotation->gmail_draft_id);
            } catch (Throwable) {
                // Keep the local record deletion resilient if Gmail cleanup fails.
            }
        }

        if ($this->isLocalGeneratedAttachment($quotation->attachment)) {
            Storage::disk('public')->delete($quotation->attachment);
        }

        $quotation->delete();

        return redirect()
            ->to('/admin/quotations')
            ->with('success', 'Quotation deleted successfully.');
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
            'from' => data_get($message, 'from.emailAddress.address'),
            'to' => data_get($message, 'toRecipients', []),
            'cc' => data_get($message, 'ccRecipients', []),
            'date' => $message['receivedDateTime'] ?? null,
            'isRead' => (bool) ($message['isRead'] ?? false),
            'preview' => (string) data_get($message, 'bodyPreview', ''),
            'body' => (string) data_get($message, 'body.content', ''),
            'raw' => $message,
        ];
    }

    /**
     * @param  array<int, array{product:\App\Models\Product, matched_keyword:string, matched_keywords:array<int, string>}>  $matches
     * @return array{0: ?\App\Models\Product, 1: ?EmailTemplate, 2: ?string}
     */
    private function resolveQuotationTarget($matches): array
    {
        foreach ($matches as $match) {
            $product = $match['product'];
            $template = EmailTemplate::query()
                ->where('product_id', (string) $product->id)
                ->orderBy('id')
                ->first();

            if ($template) {
                return [$product, $template, $match['matched_keyword'] ?? null];
            }
        }

        return [null, null, null];
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function customerEmail(array $message): string
    {
        $from = (string) ($message['from'] ?? '');

        if (preg_match('/<([^>]+)>/', $from, $matches) === 1) {
            return $matches[1];
        }

        return filter_var($from, FILTER_VALIDATE_EMAIL) ? $from : '';
    }

    private function isLocalGeneratedAttachment(?string $attachment): bool
    {
        return filled($attachment) && Str::startsWith($attachment, 'quotations/');
    }

    private function gmailHeaderValue(array $message, string $name): ?string
    {
        foreach (($message['payload']['headers'] ?? []) as $header) {
            if (($header['name'] ?? null) === $name) {
                return $header['value'] ?? null;
            }
        }

        return null;
    }

    private function gmailBody(array $message): string
    {
        foreach (($message['payload']['parts'] ?? []) as $part) {
            $mimeType = $part['mimeType'] ?? null;
            $body = $part['body']['data'] ?? null;

            if ($body && in_array($mimeType, ['text/html', 'text/plain'], true)) {
                return $this->decodeGmailBody($body);
            }
        }

        $body = data_get($message, 'payload.body.data', '');

        return $body ? $this->decodeGmailBody($body) : '';
    }

    private function decodeGmailBody(string $body): string
    {
        return base64_decode(strtr($body, '-_', '+/')) ?: '';
    }
}
