<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CrmTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamsController extends Controller
{
    public function index(Request $request)
    {
        $teams = CrmTeam::with([
            'teamLead:id,name', 
            'users' => function($q) {
                $q->select('users.id', 'users.name')
                  ->withExists(['crmSessions as is_online' => fn ($query) => $query->where('status', 'online')]);
            }
        ])
            ->when($request->boolean('active_only'), fn ($query) => $query->active())
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $teams]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'team_lead_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $team = CrmTeam::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Team created successfully',
            'data' => $team->load(['teamLead:id,name', 'users:id,name']),
        ], 201);
    }

    public function update(Request $request, CrmTeam $team)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'team_lead_id' => ['nullable', 'exists:users,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $team->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Team updated successfully',
            'data' => $team->fresh(['teamLead:id,name', 'users:id,name']),
        ]);
    }

    public function addMembers(Request $request, CrmTeam $team)
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'role_in_team' => ['sometimes', Rule::in(['lead', 'agent', 'observer'])],
        ]);

        $role = $data['role_in_team'] ?? 'agent';
        $sync = [];
        foreach ($data['user_ids'] as $userId) {
            $sync[$userId] = ['role_in_team' => $role];
        }
        $team->users()->syncWithoutDetaching($sync);

        return response()->json([
            'status' => true,
            'message' => 'Members added successfully',
            'data' => $team->fresh(['teamLead:id,name', 'users:id,name']),
        ]);
    }

    public function removeMember(CrmTeam $team, User $user)
    {
        $team->users()->detach($user->id);

        return response()->json([
            'status' => true,
            'message' => 'Member removed successfully',
            'data' => $team->fresh(['teamLead:id,name', 'users:id,name']),
        ]);
    }
}
