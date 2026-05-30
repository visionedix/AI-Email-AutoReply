<?php

namespace Tests\Unit;

use App\Admin\Models\Product;
use App\Services\ProductKeywordMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductKeywordMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_matches_products_by_product_name_when_email_contains_an_inquiry_keyword(): void
    {
        $first = Product::create([
            'product_name' => 'SKF Bearing',
            'sku' => 'SKF-001',
            'unit' => 'pcs',
            'per_unit_price' => 120.00,
            'keyword_search' => 'bearing, ball bearing, skf bearing',
        ]);

        $second = Product::create([
            'product_name' => 'Industrial Motor',
            'sku' => 'MOTOR-001',
            'unit' => 'pcs',
            'per_unit_price' => 250.00,
            'keyword_search' => 'motor, electric motor',
        ]);

        $matches = app(ProductKeywordMatcher::class)->matchProducts('Inquiry: We need SKF Bearing for this machine.');

        $this->assertCount(1, $matches);
        $this->assertSame($first->id, $matches->first()['product']->id);
        $this->assertSame('SKF Bearing', $matches->first()['matched_keyword']);
    }
}
