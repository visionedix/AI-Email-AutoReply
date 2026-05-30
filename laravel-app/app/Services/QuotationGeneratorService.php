<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class QuotationGeneratorService
{
    /**
     * @param  array<string, mixed>  $message
     * @return array{subject: string, body: string}
     */
    public function generate(Product $product, EmailTemplate $template, array $message): array
    {
        $subject = $this->replaceTokens((string) $template->template_subject, $product, $message);
        $body = $this->replaceTokens((string) $template->template_body, $product, $message);

        return [
            'subject' => $subject,
            'body' => $this->appendProductDetails($body, $product, $template, $message),
        ];
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function replaceTokens(string $content, Product $product, array $message): string
    {
        $replacements = [
            '{customer_name}' => $this->customerName($message),
            '{product_name}' => (string) ($product->product_name ?? $product->name ?? ''),
            '{date}' => $this->messageDate($message),
            '{company_name}' => config('app.name', 'Laravel'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function appendProductDetails(string $body, Product $product, EmailTemplate $template, array $message): string
    {
        $details = $this->productDetailsSection($product, $template, $message);

        if ($this->looksLikeHtml($body)) {
            return $body."\n".$details;
        }

        return trim($body."\n\n".$details);
    }

    private function productDetailsSection(Product $product, EmailTemplate $template, array $message): string
    {
        $lines = [
            'Product Name: '.($product->product_name ?? '-'),
            'SKU: '.($product->sku ?? '-'),
            'Unit: '.($product->unit ?? '-'),
            'Price: '.($product->per_unit_price ?? '-'),
        ];

        if (filled($product->product_details)) {
            $lines[] = 'Product Details: '.$product->product_details;
        }

        if (filled($template->template_other_details)) {
            $lines[] = 'Template Details: '.$template->template_other_details;
        }

        $lines[] = 'Customer: '.$this->customerName($message);

        if ($this->looksLikeHtml($template->template_body)) {
            return '<hr><div><strong>Product Details</strong><br>'.implode('<br>', array_map('e', $lines)).'</div>';
        }

        return implode("\n", $lines);
    }

    private function looksLikeHtml(string $content): bool
    {
        return Str::contains(Str::lower($content), ['<html', '<body', '<div', '<p', '<br', '<span', '<table']);
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function customerName(array $message): string
    {
        $from = (string) ($message['from'] ?? '');
        $name = trim((string) preg_replace('/<[^>]+>/', '', $from));

        if ($name !== '' && ! Str::contains($name, '@')) {
            return $name;
        }

        $email = $this->customerEmail($message);

        return $email !== '' ? Str::before($email, '@') : 'Customer';
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

    /**
     * @param  array<string, mixed>  $message
     */
    private function messageDate(array $message): string
    {
        $date = $message['date'] ?? null;

        if (blank($date)) {
            return now()->format('Y-m-d');
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return now()->format('Y-m-d');
        }
    }
}
