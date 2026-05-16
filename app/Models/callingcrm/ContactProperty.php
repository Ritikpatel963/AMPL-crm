<?php

namespace App\Models\callingcrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactProperty extends Model
{
    use HasFactory;

    public const TYPE_OPTIONS = [
        'text',
        'number',
        'date',
        'dropdown',
    ];

    protected $fillable = [
        'name',
        'data_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(LeadPropertyValue::class, 'property_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
