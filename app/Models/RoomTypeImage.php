<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTypeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'public_id',
        'url',
        'secure_url',
        'format',
        'width',
        'height',
        'bytes',
        'caption',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'bytes' => 'integer',
        'sort_order' => 'integer',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}