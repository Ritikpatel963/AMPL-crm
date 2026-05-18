<?php

namespace App\Models\callingcrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_OPTIONS = [
        self::STATUS_ACTIVE,
        self::STATUS_PAUSED,
    ];

    public const DISTRIBUTION_ON_DEMAND = 'on_demand';
    public const DISTRIBUTION_AUTO_ASSIGN = 'auto_assign';
    public const DISTRIBUTION_OPTIONS = [
        self::DISTRIBUTION_ON_DEMAND,
        self::DISTRIBUTION_AUTO_ASSIGN,
    ];

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_OPTIONS = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
    ];

    protected $fillable = [
        'pipeline_id',
        'name',
        'manager_id',
        'status',
        'distribution',
        'priority',
    ];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function agents()
    {
        return $this->belongsToMany(User::class, 'campaign_user')->withTimestamps();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function callLogs()
    {
        return $this->hasManyThrough(CallLog::class, Lead::class);
    }

    public function assignmentRules()
    {
        return $this->hasMany(AssignmentRule::class);
    }
}
