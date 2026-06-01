<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use HasApiTokens;
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'role',
        'email',
        'password',
        'status',
        'approval_status',
        'phone_number',
        'employee_id',
        'reporting_manager_id',
        'crm_status',
        'lead_assignment_enabled',
        'expires_at',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lead_assignment_enabled' => 'boolean',
            'expires_at' => 'date',
            'last_seen_at' => 'datetime',
        ];
    }

    public function assignedCustomers()
    {
        return $this->hasMany(AgentCustomerAssignment::class, 'agent_id');
    }

    public function assignedAgent()
    {
        return $this->hasOne(AgentCustomerAssignment::class, 'customer_id')->latestOfMany();
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }


    public function vendorDetail()
    {
        return $this->hasOne(VendorDetail::class);
    }
    public function vendorProducts()
    {
        return $this->hasMany(VendorProduct::class);
    }

    public function reportingManager()
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    public function reportees()
    {
        return $this->hasMany(User::class, 'reporting_manager_id');
    }

    public function ledCrmTeams()
    {
        return $this->hasMany(CrmTeam::class, 'team_lead_id');
    }

    public function crmTeams()
    {
        return $this->belongsToMany(CrmTeam::class, 'crm_team_user', 'user_id', 'team_id')
            ->withPivot('role_in_team')
            ->withTimestamps();
    }

    public function managedCampaigns()
    {
        return $this->hasMany(Campaign::class, 'manager_id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_user')
            ->withPivot('role', 'assigned_leads_count', 'is_active')
            ->withTimestamps();
    }

    public function createdLeads()
    {
        return $this->hasMany(Lead::class);
    }

    public function leads()
    {
        return $this->createdLeads();
    }

    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_user_id');
    }

    public function uploadedContactLists()
    {
        return $this->hasMany(ContactList::class, 'uploaded_by');
    }

    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    public function leadDispositions()
    {
        return $this->hasMany(LeadDisposition::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function createdFollowUps()
    {
        return $this->hasMany(FollowUp::class, 'created_by');
    }

    public function crmSessions()
    {
        return $this->hasMany(CrmUserSession::class);
    }

    public function breaks()
    {
        return $this->hasMany(UserBreak::class);
    }

    public function crmNotes()
    {
        return $this->hasMany(CrmNote::class);
    }

    public function communicationEvents()
    {
        return $this->hasMany(CommunicationEvent::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(CrmTask::class, 'assigned_to');
    }

    public function createdTasks()
    {
        return $this->hasMany(CrmTask::class, 'created_by');
    }

    public function savedFilters()
    {
        return $this->hasMany(SavedFilter::class);
    }

    public function reportExports()
    {
        return $this->hasMany(ReportExport::class);
    }

    public function assignmentRules()
    {
        return $this->hasMany(AssignmentRule::class);
    }

    public function scopeActiveCrm($query)
    {
        return $query->where('crm_status', 'active');
    }

    public function scopeAssignmentEnabled($query)
    {
        return $query->where('lead_assignment_enabled', true);
    }

    public function scopeAgents($query)
    {
        return $query->whereIn('role', ['agent', 'subadmin']);
    }

    public function scopeTeamLeads($query)
    {
        return $query->agents()->whereHas('ledCrmTeams');
    }

    public function scopeReportingTo($query, int $managerId)
    {
        return $query->where('reporting_manager_id', $managerId);
    }

    public function getRoleLabelAttribute()
    {
        return match ($this->role) {
            'subadmin' => 'Admin',
            'agent' => 'Executive',
            default => ucfirst($this->role),
        };
    }

    public function getIsOnlineAttribute()
    {
        return $this->crmSessions()->where('status', 'online')->exists();
    }

    public function getCurrentBreakDurationAttribute()
    {
        $break = $this->breaks()->whereNull('ended_at')->first();
        return $break ? $break->started_at->diffInMinutes(now()) : 0;
    }

    public function getAssignedCampaignsCountAttribute()
    {
        return $this->campaigns()->count();
    }
}
