<?php

namespace App\Services\AI;

use App\Contracts\QuotationAiService;
use App\Models\EmailTemplate;
use App\Models\Product;
use App\Services\QuotationGeneratorService;

class RuleBasedQuotationAiService implements QuotationAiService
{
    public function __construct(
        private readonly QuotationGeneratorService $generator,
    ) {
    }

    public function identifyProduct(array $message, array $matches): array
    {
        $match = $matches[0] ?? null;

        if (! $match) {
            return [
                'product' => null,
                'matched_keyword' => null,
                'matched_keywords' => [],
                'confidence' => 0.0,
                'reason' => 'No product keywords were matched.',
            ];
        }

        return [
            'product' => $match['product'],
            'matched_keyword' => $match['matched_keyword'] ?? null,
            'matched_keywords' => $match['matched_keywords'] ?? [],
            'confidence' => 0.6,
            'reason' => 'Keyword-based fallback selected the first matching product.',
        ];
    }

    public function generateQuotation(Product $product, EmailTemplate $template, array $message, ?string $matchedKeyword = null): array
    {
        return $this->generator->generate($product, $template, $message);
    }
}
