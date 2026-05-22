<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pipeline_id',
        'name',
        'code',
        'category',
        'color',
        'is_closed',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_closed' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function tags()
    {
        return $this->hasMany(StageTag::class, 'stage_id')->orderBy('sort_order');
    }

    public function transitions()
    {
        return $this->belongsToMany(
            self::class,
            'lead_stage_transitions',
            'from_stage_id',
            'to_stage_id'
        )->withTimestamps()->orderBy('sort_order');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }

    public function dispositions()
    {
        return $this->hasMany(Disposition::class, 'stage_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_closed', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getLeadCountAttribute()
    {
        return $this->leads()->count();
    }

    public function getConversionPercentAttribute()
    {
        $total = $this->pipeline?->leads()->count() ?? 0;
        if ($total === 0) return 0;
        return round(($this->lead_count / $total) * 100, 2);
    }
}
