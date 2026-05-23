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
        'drow_images',
    ];

    protected $casts = [
        'per_unit_price' => 'decimal:2',
        'drow_images' => 'array',
    ];
}
