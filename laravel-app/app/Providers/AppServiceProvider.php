<?php

namespace App\Providers;

use App\Contracts\QuotationAiService;
use App\Models\Quotation;
use App\Policies\QuotationPolicy;
use App\Services\AI\OpenAiQuotationAiService;
use App\Services\AI\QuotationAiManager;
use App\Services\AI\RuleBasedQuotationAiService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(QuotationAiService::class, function ($app) {
            return new QuotationAiManager(
                $app->make(OpenAiQuotationAiService::class),
                $app->make(RuleBasedQuotationAiService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Quotation::class, QuotationPolicy::class);
    }
}
