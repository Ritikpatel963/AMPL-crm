<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'campaign_id',
        'user_id',
        'disposition_id',
        'direction',
        'status',
        'provider_call_id',
        'phone_number',
        'duration',
        'duration_seconds',
        'ring_duration_seconds',
        'notes',
        'called_at',
        'started_at',
        'answered_at',
        'ended_at',
        'recording_url',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'duration_seconds' => 'integer',
            'ring_duration_seconds' => 'integer',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'answered_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
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

    public function disposition()
    {
        return $this->belongsTo(Disposition::class);
    }

    public function leadDisposition()
    {
        return $this->hasOne(LeadDisposition::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function scopeConnected($query)
    {
        return $query->whereIn('status', ['connected', 'answered']);
    }

    public function scopeNotConnected($query)
    {
        return $query->whereIn('status', ['not_connected', 'busy', 'no_answer', 'failed', 'missed']);
    }

    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereDate('started_at', '>=', $from)->whereDate('started_at', '<=', $to);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function getDurationLabelAttribute()
    {
        if (!$this->duration_seconds) return '0s';
        $h = intdiv($this->duration_seconds, 3600);
        $m = intdiv($this->duration_seconds % 3600, 60);
        $s = $this->duration_seconds % 60;
        $parts = [];
        if ($h) $parts[] = "{$h}h";
        if ($m) $parts[] = "{$m}m";
        $parts[] = "{$s}s";
        return implode(' ', $parts);
    }

    public function getWasConnectedAttribute()
    {
        return in_array($this->status, ['connected', 'answered']);
    }
}
