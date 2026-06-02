<?php

namespace App\Traits\CallingCrm;

use App\Models\Lead;
use Illuminate\Support\Facades\Auth;

trait LeadAccess
{
    public function canAccessLead($user, Lead $lead): bool
    {
        if (! $user) {
            return Auth::guard('admin')->check();
        }

        if ($user->role === 'subadmin') {
            return true;
        }

        if ($user->role === 'agent') {
            return (int) $lead->assigned_user_id === (int) $user->id
                || (
                    $lead->assigned_user_id === null
                    && $lead->campaign()
                        ->visibleToUser($user->id)
                        ->where(function ($query) {
                            $query->where('status', '!=', 'paused')
                                ->orWhere('hide_paused_from_agents', false);
                        })
                        ->exists()
                );
        }

        \Illuminate\Support\Facades\Log::warning('Unauthorized CRM lead access attempt.', [
            'user_id' => $user->id,
            'role' => $user->role,
            'lead_id' => $lead->id,
        ]);

        return false;
    }
}
