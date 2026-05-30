<?php

namespace App\Admin\Models;

use App\Models\Product as BaseProduct;

class Product extends BaseProduct
{
    protected $fillable = [
        'product_name',
        'sku',
        'unit',
        'per_unit_price',
        'product_details',
        'notes',
        'image',
        'keyword_search',
        'other_details',
        'specification',
        'quotation_documents',
        'drow_image_1',
        'drow_image_2',
        'drow_image_3',
    ];

    protected $casts = [
        'per_unit_price' => 'decimal:2',
        'drow_image_1' => 'array',
        'drow_image_2' => 'array',
        'drow_image_3' => 'array',
    ];
}
