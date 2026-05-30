<?php

namespace App\Jobs;

use App\Models\Quotation;
use App\Services\Mail\GmailDraftService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CreateQuotationDraftJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $quotationId)
    {
    }

    public function handle(GmailDraftService $drafts): void
    {
        $quotation = Quotation::query()->findOrFail($this->quotationId);

        try {
            if (filled($quotation->gmail_draft_id)) {
                $result = $drafts->updateQuotationDraft(
                    $quotation->gmail_draft_id,
                    $quotation->subject,
                    $quotation->body,
                    (string) $quotation->attachment,
                    $quotation->customer_email
                );
            } else {
                $result = $drafts->createQuotationDraft(
                    $quotation->subject,
                    $quotation->body,
                    (string) $quotation->attachment,
                    $quotation->customer_email
                );
            }

            $quotation->update([
                'gmail_draft_id' => $result['draft_id'] ?? $quotation->gmail_draft_id,
                'status' => 'draft',
            ]);
        } catch (Throwable $throwable) {
            $quotation->update([
                'status' => 'failed',
            ]);

            throw $throwable;
        }
    }
}
