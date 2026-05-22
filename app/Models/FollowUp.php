<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'campaign_id',
        'user_id',
        'created_by',
        'call_log_id',
        'scheduled_at',
        'completed_at',
        'status',
        'note',
        'is_missed',
        'is_system_generated',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'is_missed' => 'boolean',
            'is_system_generated' => 'boolean',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function callLog()
    {
        return $this->belongsTo(CallLog::class);
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'scheduled')->where('scheduled_at', '<=', now());
    }

    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('scheduled_at', today())->where('status', 'scheduled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())->where('status', 'scheduled');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
