<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePermissionDefault extends Model
{
    protected $fillable = [
        'role',
        'module_key',
        'is_allowed',
    ];

    protected function casts(): array
    {
        return [
            'is_allowed' => 'boolean',
        ];
    }
}
