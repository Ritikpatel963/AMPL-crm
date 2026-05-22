<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmTeam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'team_lead_id',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teamLead()
    {
        return $this->belongsTo(User::class, 'team_lead_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'crm_team_user', 'team_id', 'user_id')
            ->withPivot('role_in_team')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
