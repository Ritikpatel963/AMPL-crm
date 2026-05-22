<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmBusinessProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_name',
        'phone',
        'address',
        'state',
        'pincode',
        'gst_number',
        'working_days',
        'work_start_time',
        'work_end_time',
        'timezone',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'work_start_time' => 'string',
            'work_end_time' => 'string',
        ];
    }

    public function pipelines()
    {
        return $this->hasMany(Pipeline::class, 'business_profile_id');
    }

    public function contactProperties()
    {
        return $this->hasMany(ContactProperty::class, 'business_profile_id');
    }

    public function leadPriorityRules()
    {
        return $this->hasMany(LeadPriorityRule::class, 'business_profile_id')->orderBy('sort_order');
    }

    public function users()
    {
        return User::whereIn('role', ['agent', 'subadmin']);
    }

    public function getWorkingHoursLabelAttribute()
    {
        if (!$this->work_start_time || !$this->work_end_time) return null;
        return date('h:i A', strtotime($this->work_start_time)) . ' - ' . date('h:i A', strtotime($this->work_end_time));
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->address, $this->state, $this->pincode]);
        return implode(', ', $parts);
    }
}
