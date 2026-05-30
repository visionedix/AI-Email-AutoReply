<?php

namespace App\Admin\Models;

use App\Models\EmailTemplate as BaseEmailTemplate;

class EmailTemplate extends BaseEmailTemplate
{
    protected $fillable = [
        'product_id',
        'template_name',
        'template_subject',
        'template_body',
        'template_other_details',
        'template_document',
        'keyword_search',
    ];
}
