<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactProperty extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_profile_id',
        'name',
        'slug',
        'data_type',
        'options',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function businessProfile()
    {
        return $this->belongsTo(CrmBusinessProfile::class, 'business_profile_id');
    }

    public function values()
    {
        return $this->hasMany(LeadPropertyValue::class, 'property_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
