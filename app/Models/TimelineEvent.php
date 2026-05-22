<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'event_type',
        'title',
        'description',
        'payload',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('occurred_at');
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereDate('occurred_at', '>=', $from)->whereDate('occurred_at', '<=', $to);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }
}
