<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    protected $fillable = [
        'user_id', 'title', 'image', 'description',
        'prep_time', 'servings', 'cuisine', 'difficulty',
    ];

    protected $casts = [
        'prep_time' => 'integer',
        'servings' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}