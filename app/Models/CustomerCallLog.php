<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCallLog extends Model
{
    protected $fillable = [
        'customer_id',
        'agent_id',
        'phone_number',
        'direction',
        'status',
        'duration_seconds',
        'started_at',
        'ended_at',
        'recording_url',
        'metadata',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
