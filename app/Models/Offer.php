<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'title',
        'discount',
        'description',
        'valid_until',
        'terms',
        'image_path',
        'badge',
        'badge_variant',
        'offer_type',
        'icon',
        'is_active',
        'order',
    ];

    protected $casts = [
        'terms' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
}
