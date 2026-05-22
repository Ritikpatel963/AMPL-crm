<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmUserSession extends Model
{
    use HasFactory;

    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'logged_in_at',
        'logged_out_at',
        'status',
        'ip_address',
        'user_agent',
        'break_minutes',
    ];

    protected function casts(): array
    {
        return [
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
            'break_minutes' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(UserBreak::class, 'session_id');
    }

    public function getLoginDurationAttribute()
    {
        if (!$this->logged_in_at) return 0;
        $end = $this->logged_out_at ?? now();
        return $this->logged_in_at->diffInMinutes($end);
    }

    public function getTotalBreakDurationAttribute()
    {
        return $this->breaks()->sum('duration_seconds');
    }
}
