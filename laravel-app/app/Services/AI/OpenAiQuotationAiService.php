<?php

namespace App\Services\AI;

use App\Contracts\QuotationAiService;
use App\Models\EmailTemplate;
use App\Models\Product;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAiQuotationAiService implements QuotationAiService
{
    public function identifyProduct(array $message, array $matches): array
    {
        if ($matches === []) {
            return [
                'product' => null,
                'matched_keyword' => null,
                'matched_keywords' => [],
                'confidence' => 0.0,
                'reason' => 'No matching products were available to score.',
            ];
        }

        $response = $this->sendJsonResponse(
            'product selection',
            $this->productSelectionPrompt($message, $matches),
            [
                'name' => 'product_selection',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'selected_product_id' => [
                            'type' => ['integer', 'null'],
                        ],
                        'matched_keyword' => [
                            'type' => ['string', 'null'],
                        ],
                        'confidence' => [
                            'type' => 'number',
                            'minimum' => 0,
                            'maximum' => 1,
                        ],
                        'reason' => [
                            'type' => 'string',
                        ],
                    ],
                    'required' => ['selected_product_id', 'matched_keyword', 'confidence', 'reason'],
                    'additionalProperties' => false,
                ],
            ]
        );

        $selectedProductId = Arr::get($response, 'selected_product_id');
        $matchedKeyword = Arr::get($response, 'matched_keyword');

        if ($selectedProductId === null) {
            return [
                'product' => null,
                'matched_keyword' => is_string($matchedKeyword) ? $matchedKeyword : null,
                'matched_keywords' => [],
                'confidence' => (float) Arr::get($response, 'confidence', 0.0),
                'reason' => (string) Arr::get($response, 'reason', 'The model did not select a product.'),
            ];
        }

        foreach ($matches as $match) {
            $product = $match['product'] ?? null;

            if ($product instanceof Product && (int) $product->id === (int) $selectedProductId) {
                return [
                    'product' => $product,
                    'matched_keyword' => is_string($matchedKeyword) && $matchedKeyword !== '' ? $matchedKeyword : ($match['matched_keyword'] ?? null),
                    'matched_keywords' => $match['matched_keywords'] ?? [],
                    'confidence' => (float) Arr::get($response, 'confidence', 0.0),
                    'reason' => (string) Arr::get($response, 'reason', 'Product selected by OpenAI.'),
                ];
            }
        }

        return [
            'product' => null,
            'matched_keyword' => is_string($matchedKeyword) ? $matchedKeyword : null,
            'matched_keywords' => [],
            'confidence' => (float) Arr::get($response, 'confidence', 0.0),
            'reason' => (string) Arr::get($response, 'reason', 'OpenAI returned an unknown product selection.'),
        ];
    }

    public function generateQuotation(Product $product, EmailTemplate $template, array $message, ?string $matchedKeyword = null): array
    {
        $response = $this->sendJsonResponse(
            'quotation drafting',
            $this->quotationPrompt($product, $template, $message, $matchedKeyword),
            [
                'name' => 'quotation_draft',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'subject' => [
                            'type' => 'string',
                        ],
                        'body' => [
                            'type' => 'string',
                        ],
                    ],
                    'required' => ['subject', 'body'],
                    'additionalProperties' => false,
                ],
            ]
        );

        $subject = trim((string) Arr::get($response, 'subject', ''));
        $body = trim((string) Arr::get($response, 'body', ''));

        if ($subject === '' || $body === '') {
            throw new RuntimeException('OpenAI returned an incomplete quotation draft.');
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    private function sendJsonResponse(string $purpose, string $prompt, array $jsonSchema): array
    {
        $response = $this->http()->post($this->responsesUrl(), [
            'model' => $this->model(),
            'input' => [
                [
                    'role' => 'system',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $this->systemPrompt($purpose),
                        ],
                    ],
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => $jsonSchema['name'],
                    'schema' => $jsonSchema['schema'],
                    'strict' => true,
                ],
            ],
            'temperature' => 0.2,
        ])->throw()->json();

        $payload = $this->extractOutputText($response);

        if ($payload === '') {
            throw new RuntimeException('OpenAI returned an empty response.');
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('OpenAI returned invalid JSON.');
        }

        return $decoded;
    }

    private function systemPrompt(string $purpose): string
    {
        return match ($purpose) {
            'product selection' => 'You analyze incoming customer emails and choose the best matching product from the provided candidates. Return only valid JSON that matches the schema.',
            'quotation drafting' => 'You write concise, professional quotation drafts for customer emails. Use only the provided product and template data. Return only valid JSON that matches the schema.',
            default => 'Return only valid JSON that matches the requested schema.',
        };
    }

    private function productSelectionPrompt(array $message, array $matches): string
    {
        $messageBlock = $this->emailBlock($message);
        $candidateLines = collect($matches)->map(function (array $match): string {
            $product = $match['product'];

            return sprintf(
                '- id=%s | product=%s | sku=%s | matched_keyword=%s | keywords=%s',
                (string) ($product?->id ?? ''),
                (string) ($product?->product_name ?? ''),
                (string) ($product?->sku ?? ''),
                (string) ($match['matched_keyword'] ?? ''),
                implode(', ', (array) ($match['matched_keywords'] ?? []))
            );
        })->implode("\n");

        return trim(<<<PROMPT
Analyze the email and pick exactly one product from the candidate list if the email is clearly asking about that item.

Email:
{$messageBlock}

Candidates:
{$candidateLines}

Rules:
- Pick the single best matching product.
- Use the candidate product id in selected_product_id.
- If the email does not match any candidate, set selected_product_id to null.
- matched_keyword should be the keyword phrase that best explains the selection.
- confidence must be between 0 and 1.
PROMPT);
    }

    private function quotationPrompt(Product $product, EmailTemplate $template, array $message, ?string $matchedKeyword): string
    {
        $messageBlock = $this->emailBlock($message);
        $productBlock = $this->formatBlock([
            'product_id: '.($product->id ?? ''),
            'product_name: '.($product->product_name ?? ''),
            'sku: '.($product->sku ?? ''),
            'unit: '.($product->unit ?? ''),
            'per_unit_price: '.($product->per_unit_price ?? ''),
            'product_details: '.($product->product_details ?? ''),
            'notes: '.($product->notes ?? ''),
            'other_details: '.($product->other_details ?? ''),
            'specification: '.($product->specification ?? ''),
            'matched_keyword: '.($matchedKeyword ?? ''),
        ]);
        $templateBlock = $this->formatBlock([
            'template_name: '.($template->template_name ?? ''),
            'template_subject: '.($template->template_subject ?? ''),
            'template_body: '.($template->template_body ?? ''),
            'template_other_details: '.($template->template_other_details ?? ''),
        ]);

        return trim(
            "Draft a quotation email for the customer using the provided product data and template.\n\n"
            ."Important rules:\n"
            ."- Do not invent prices, SKUs, quantities, or product facts that are not provided.\n"
            ."- Keep the tone professional, clear, and suitable for a Gmail draft.\n"
            ."- Preserve HTML if the template body is HTML; otherwise return plain text.\n"
            ."- Return a concise subject and a ready-to-send body.\n\n"
            ."Email context:\n{$messageBlock}\n\n"
            ."Product:\n{$productBlock}\n\n"
            ."Template:\n{$templateBlock}"
        );
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function formatBlock(array $lines): string
    {
        return implode("\n", $lines);
    }

    private function emailBlock(array $message): string
    {
        return implode("\n", [
            'subject: '.(string) ($message['subject'] ?? ''),
            'from: '.(string) ($message['from'] ?? ''),
            'to: '.(string) ($message['to'] ?? ''),
            'cc: '.(string) ($message['cc'] ?? ''),
            'date: '.(string) ($message['date'] ?? ''),
            'body: '.trim((string) ($message['body'] ?? '')),
        ]);
    }

    private function extractOutputText(array $response): string
    {
        if (is_string(data_get($response, 'output_text'))) {
            return trim((string) data_get($response, 'output_text'));
        }

        foreach ((array) data_get($response, 'output', []) as $outputItem) {
            foreach ((array) ($outputItem['content'] ?? []) as $content) {
                $type = $content['type'] ?? null;

                if (in_array($type, ['output_text', 'text'], true) && isset($content['text'])) {
                    return trim((string) $content['text']);
                }
            }
        }

        return '';
    }

    private function http(): PendingRequest
    {
        $apiKey = config('ai.openai.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        return Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('ai.timeout', 30));
    }

    private function model(): string
    {
        return (string) config('ai.openai.model', 'chat-latest');
    }

    private function responsesUrl(): string
    {
        return rtrim((string) config('ai.openai.base_url', 'https://api.openai.com/v1'), '/').'/responses';
    }
}
