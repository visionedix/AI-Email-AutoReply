<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductKeywordMatcher
{
    /**
     * Return products whose comma-separated keyword list matches the provided content.
     *
     * @return Collection<int, array{product: Product, matched_keyword: string, matched_keywords: array<int, string>}>
     */
    public function matchProducts(string $content): Collection
    {
        $normalizedContent = $this->normalize($content);

        if ($normalizedContent === '' || ! $this->hasQuotationIntent($normalizedContent)) {
            return collect();
        }

        return Product::query()
            ->orderBy('id')
            ->get()
            ->map(function (Product $product) use ($normalizedContent): ?array {
                $candidateTerms = collect([
                    $product->product_name,
                    $product->sku,
                ])
                    ->merge(explode(',', (string) $product->keyword_search))
                    ->map(fn (string $keyword): string => trim($keyword))
                    ->filter()
                    ->values();

                $matchedTerm = $candidateTerms->first(function (string $term) use ($normalizedContent): bool {
                    return Str::contains($normalizedContent, $this->normalize($term));
                });

                if (! $matchedTerm) {
                    return null;
                }

                return [
                    'product' => $product,
                    'matched_keyword' => $matchedTerm,
                    'matched_keywords' => [$matchedTerm],
                ];
            })
            ->filter()
            ->values();
    }

    private function hasQuotationIntent(string $content): bool
    {
        return Str::contains($content, [
            'inquiry',
            'enquiry',
            'requirement',
            'requirements',
            'quotation',
            'quote',
            'rfq',
            'price request',
            'request for quotation',
        ]);
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->lower()->squish()->toString();
    }
}
