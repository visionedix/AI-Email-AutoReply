<?php

namespace App\Contracts;

use App\Models\EmailTemplate;
use App\Models\Product;

interface QuotationAiService
{
    /**
     * @param  array<string, mixed>  $message
     * @param  array<int, array{product: Product, matched_keyword: string, matched_keywords: array<int, string>}>  $matches
     * @return array{product: ?Product, matched_keyword: ?string, matched_keywords: array<int, string>, confidence?: float, reason?: string}
     */
    public function identifyProduct(array $message, array $matches): array;

    /**
     * @param  array<string, mixed>  $message
     * @return array{subject: string, body: string}
     */
    public function generateQuotation(Product $product, EmailTemplate $template, array $message, ?string $matchedKeyword = null): array;
}
