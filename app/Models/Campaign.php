<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pipeline_id',
        'manager_id',
        'name',
        'description',
        'status',
        'distribution',
        'priority',
        'is_pinned',
        'hide_paused_from_agents',
        'lead_chunk_size',
        'starts_at',
        'ends_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'hide_paused_from_agents' => 'boolean',
            'lead_chunk_size' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'campaign_user')
            ->withPivot('role', 'assigned_leads_count', 'is_active')
            ->withTimestamps();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function contactLists()
    {
        return $this->hasMany(ContactList::class);
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    public function leadDispositions()
    {
        return $this->hasMany(LeadDisposition::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function communicationEvents()
    {
        return $this->hasMany(CommunicationEvent::class);
    }

    public function tasks()
    {
        return $this->hasMany(CrmTask::class);
    }

    public function assignmentRules()
    {
        return $this->hasMany(AssignmentRule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByPipeline($query, int $pipelineId)
    {
        return $query->where('pipeline_id', $pipelineId);
    }

    public function scopeVisibleToUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereHas('users', fn ($q) => $q->where('user_id', $userId))
              ->orWhere('manager_id', $userId);
        });
    }

    public function getTotalLeadsAttribute()
    {
        return $this->leads()->count();
    }

    public function getAssignedLeadsAttribute()
    {
        return $this->leads()->whereNotNull('assigned_user_id')->count();
    }

    public function getUnassignedLeadsAttribute()
    {
        return $this->leads()->whereNull('assigned_user_id')->count();
    }

    public function getCalledLeadsAttribute()
    {
        return $this->leads()->whereNotNull('last_call_at')->count();
    }

    public function getClosedLeadsAttribute()
    {
        return $this->leads()->whereIn('status', ['converted', 'lost', 'closed'])->count();
    }

    public function getRescheduledLeadsAttribute()
    {
        return $this->leads()->where('status', 'in_progress')->whereNotNull('next_follow_up_at')->count();
    }
}
