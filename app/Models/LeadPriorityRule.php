<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadPriorityRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_profile_id',
        'name',
        'code',
        'sort_order',
        'is_locked',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_locked' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function businessProfile()
    {
        return $this->belongsTo(CrmBusinessProfile::class, 'business_profile_id');
    }
}
