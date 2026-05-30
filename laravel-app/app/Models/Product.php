<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
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
            'drow_image_1' => 'array',
            'drow_image_2' => 'array',
            'drow_image_3' => 'array',
        ];
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }
}
