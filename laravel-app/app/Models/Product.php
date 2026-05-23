<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'product_name',
    'sku',
    'unit',
    'per_unit_price',
    'product_details',
    'notes',
    'image',
    'drow_image_1',
    'drow_image_2',
    'drow_image_3',
])]
class Product extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'per_unit_price' => 'decimal:2',
            'drow_images' => 'array',
        ];
    }
}
