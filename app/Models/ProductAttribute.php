<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'name',
        'values',
        'status',
    ];

    protected $casts = [
        'values' => 'array',
        'status' => 'boolean',
    ];
}
