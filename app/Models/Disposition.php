<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disposition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pipeline_id',
        'stage_id',
        'tag_id',
        'name',
        'type',
        'requires_follow_up',
        'requires_note',
        'marks_lead_closed',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_follow_up' => 'boolean',
            'requires_note' => 'boolean',
            'marks_lead_closed' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function tag()
    {
        return $this->belongsTo(StageTag::class, 'tag_id');
    }

    public function leadDispositions()
    {
        return $this->hasMany(LeadDisposition::class);
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPipeline($query, int $pipelineId)
    {
        return $query->where('pipeline_id', $pipelineId);
    }

    public function scopeRequiresFollowUp($query)
    {
        return $query->where('requires_follow_up', true);
    }
}
