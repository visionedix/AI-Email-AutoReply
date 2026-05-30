<?php

namespace App\Services\AI;

use App\Contracts\QuotationAiService;
use App\Models\EmailTemplate;
use App\Models\Product;
use RuntimeException;
use Throwable;

class QuotationAiManager implements QuotationAiService
{
    public function __construct(
        private readonly OpenAiQuotationAiService $openAi,
        private readonly RuleBasedQuotationAiService $ruleBased,
    ) {
    }

    public function identifyProduct(array $message, array $matches): array
    {
        return match ($this->driver()) {
            'openai' => $this->fallbackToRuleBased(
                fn () => $this->openAi->identifyProduct($message, $matches),
                fn () => $this->ruleBased->identifyProduct($message, $matches)
            ),
            'rule_based' => $this->ruleBased->identifyProduct($message, $matches),
            'auto' => $this->autoIdentifyProduct($message, $matches),
            default => throw new RuntimeException('AI_DRIVER must be auto, openai, or rule_based.'),
        };
    }

    public function generateQuotation(Product $product, EmailTemplate $template, array $message, ?string $matchedKeyword = null): array
    {
        return match ($this->driver()) {
            'openai' => $this->fallbackToRuleBased(
                fn () => $this->openAi->generateQuotation($product, $template, $message, $matchedKeyword),
                fn () => $this->ruleBased->generateQuotation($product, $template, $message, $matchedKeyword)
            ),
            'rule_based' => $this->ruleBased->generateQuotation($product, $template, $message, $matchedKeyword),
            'auto' => $this->autoGenerateQuotation($product, $template, $message, $matchedKeyword),
            default => throw new RuntimeException('AI_DRIVER must be auto, openai, or rule_based.'),
        };
    }

    private function autoIdentifyProduct(array $message, array $matches): array
    {
        if (filled(config('ai.openai.api_key'))) {
            return $this->fallbackToRuleBased(
                fn () => $this->openAi->identifyProduct($message, $matches),
                fn () => $this->ruleBased->identifyProduct($message, $matches)
            );
        }

        return $this->ruleBased->identifyProduct($message, $matches);
    }

    private function autoGenerateQuotation(Product $product, EmailTemplate $template, array $message, ?string $matchedKeyword = null): array
    {
        if (filled(config('ai.openai.api_key'))) {
            return $this->fallbackToRuleBased(
                fn () => $this->openAi->generateQuotation($product, $template, $message, $matchedKeyword),
                fn () => $this->ruleBased->generateQuotation($product, $template, $message, $matchedKeyword)
            );
        }

        return $this->ruleBased->generateQuotation($product, $template, $message, $matchedKeyword);
    }

    private function driver(): string
    {
        return (string) config('ai.driver', 'auto');
    }

    /**
     * @template T
     * @param  callable(): T  $primary
     * @param  callable(): T  $fallback
     * @return T
     */
    private function fallbackToRuleBased(callable $primary, callable $fallback): mixed
    {
        try {
            return $primary();
        } catch (Throwable) {
            return $fallback();
        }
    }
}
