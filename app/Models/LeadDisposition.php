<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadDisposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'call_log_id',
        'user_id',
        'campaign_id',
        'from_stage_id',
        'to_stage_id',
        'tag_id',
        'disposition_id',
        'call_status',
        'remark',
        'disposed_at',
    ];

    protected function casts(): array
    {
        return [
            'disposed_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function callLog()
    {
        return $this->belongsTo(CallLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function fromStage()
    {
        return $this->belongsTo(LeadStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(LeadStage::class, 'to_stage_id');
    }

    public function tag()
    {
        return $this->belongsTo(StageTag::class, 'tag_id');
    }

    public function disposition()
    {
        return $this->belongsTo(Disposition::class);
    }
}
