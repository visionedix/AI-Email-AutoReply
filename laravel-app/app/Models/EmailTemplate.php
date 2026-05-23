<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'template_name',
    'template_subject',
    'template_body',
    'template_other_details',
    'template_document',
    'keyword_search',
])]
class EmailTemplate extends Model
{
    use HasFactory;
}
