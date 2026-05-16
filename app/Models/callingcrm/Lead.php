<?php

namespace App\Models\callingcrm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    public const SOURCE_OPTIONS = [
        'FILE_UPLOAD',
        'WALK_IN_LEAD',
        'INCOMING_IVR',
        'WORKFLOW',
        'GOOGLE_SHEET',
        'MANUAL',
    ];

    protected $fillable = [
        'campaign_id',
        'user_id',
        'stage_id',
        'name',
        'phone',
        'email',
        'source',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function propertyValues()
    {
        return $this->hasMany(LeadPropertyValue::class);
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }
}
