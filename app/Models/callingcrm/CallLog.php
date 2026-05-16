<?php

namespace App\Models\callingcrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;

    public const STATUS_OPTIONS = [
        'connected',
        'not_connected',
        'busy',
        'no_answer',
    ];

    protected $fillable = [
        'lead_id',
        'user_id',
        'status',
        'duration',
        'notes',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
