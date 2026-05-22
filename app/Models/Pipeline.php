<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_profile_id',
        'name',
        'color',
        'description',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function businessProfile()
    {
        return $this->belongsTo(CrmBusinessProfile::class, 'business_profile_id');
    }

    public function stages()
    {
        return $this->hasMany(LeadStage::class)->orderBy('sort_order');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function dispositions()
    {
        return $this->hasMany(Disposition::class);
    }

    public function retryReasons()
    {
        return $this->hasMany(RetryReason::class);
    }

    public function stageTags()
    {
        return $this->hasManyThrough(StageTag::class, LeadStage::class, 'pipeline_id', 'stage_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
