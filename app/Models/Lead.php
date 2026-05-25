<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'pipeline_id',
        'user_id',
        'assigned_user_id',
        'stage_id',
        'tag_id',
        'source_id',
        'contact_list_id',
        'name',
        'phone',
        'email',
        'source',
        'tags',
        'status',
        'priority_bucket',
        'deal_amount',
        'currency',
        'last_call_at',
        'next_follow_up_at',
        'total_disposition_count',
        'confidential_remark',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'metadata' => 'array',
            'deal_amount' => 'decimal:2',
            'last_call_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'total_disposition_count' => 'integer',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function tag()
    {
        return $this->belongsTo(StageTag::class, 'tag_id');
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function contactList()
    {
        return $this->belongsTo(ContactList::class);
    }

    public function contactListRows()
    {
        return $this->hasMany(ContactListRow::class);
    }

    public function phoneNumbers()
    {
        return $this->hasMany(LeadPhoneNumber::class);
    }

    public function propertyValues()
    {
        return $this->hasMany(LeadPropertyValue::class);
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    public function latestCall()
    {
        return $this->hasOne(CallLog::class)->latestOfMany('started_at');
    }

    public function dispositions()
    {
        return $this->hasMany(LeadDisposition::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function notes()
    {
        return $this->hasMany(CrmNote::class);
    }

    public function timelineEvents()
    {
        return $this->hasMany(TimelineEvent::class)->orderByDesc('occurred_at');
    }

    public function communicationEvents()
    {
        return $this->hasMany(CommunicationEvent::class);
    }

    public function tasks()
    {
        return $this->hasMany(CrmTask::class);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_user_id', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_user_id');
    }

    public function scopeDueFollowUp($query)
    {
        return $query->whereNotNull('next_follow_up_at')->where('next_follow_up_at', '<=', now());
    }

    public function scopeSearchPhone($query, string $phone)
    {
        return $query->where('phone', 'like', "%{$phone}%");
    }

    public function scopeByCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeByStage($query, int $stageId)
    {
        return $query->where('stage_id', $stageId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePriorityOrdered($query)
    {
        return $query->orderByRaw("FIELD(priority_bucket, 'manual_scheduled', 'assigned_uncontacted', 'unassigned_uncontacted', 'in_progress_no_followup', 'not_connected_scheduled', 'normal') ASC");
    }

    public function getLatestRemarkAttribute()
    {
        return $this->dispositions()->latest('disposed_at')->value('remark');
    }

    public function getLatestCallStatusAttribute()
    {
        return $this->callLogs()->latest('started_at')->value('status');
    }

    public function getIsFollowupDueAttribute()
    {
        return $this->next_follow_up_at && $this->next_follow_up_at <= now();
    }

    public function getDisplayStageAttribute()
    {
        return $this->stage?->name;
    }

    public function getDisplayTagAttribute()
    {
        return $this->tag?->name;
    }

    public function getCallAttemptsCountAttribute()
    {
        return $this->callLogs()->count();
    }
}
