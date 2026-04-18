<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingContent extends Model
{
    protected $fillable = [
        'key',
        'content',
        'section',
        'description',
    ];

    protected $casts = [
        'content' => 'array',
    ];
}
