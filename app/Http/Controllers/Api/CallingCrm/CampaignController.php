<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\AssignmentRule;
use App\Models\Campaign;
use App\Models\Lead;
use App\Services\CallingCrm\LeadAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class CampaignController extends Controller
{
    public function __construct(private LeadAssignmentService $assignmentService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $campaigns = Campaign::query()
            ->with([
                'pipeline:id,name',
                'manager:id,name',
                'users' => function ($q) {
                    $q->select('users.id', 'users.name', 'users.email')
                        ->withExists(['crmSessions as is_online' => fn($query) => $query->where('status', 'online')]);
                },
            ])
            ->withCount([
                'leads',
                'leads as total_leads_count',
                'leads as assigned_leads_count' => fn($query) => $query
                    ->whereNotNull('assigned_user_id')
                    ->when($user?->role === 'agent', fn($q) => $q->where('assigned_user_id', $user->id)),
                'leads as unassigned_leads_count' => fn($query) => $query->whereNull('assigned_user_id'),
                'leads as uncontacted_leads_count' => fn($query) => $query
                    ->where('status', 'uncontacted')
                    ->when($user?->role === 'agent', fn($q) => $q->where('assigned_user_id', $user->id)),
                'leads as in_progress_leads_count' => fn($query) => $query
                    ->where('status', 'in_progress')
                    ->when($user?->role === 'agent', fn($q) => $q->where('assigned_user_id', $user->id)),
                'leads as closed_leads_count' => fn($query) => $query
                    ->whereIn('status', ['converted', 'lost', 'closed'])
                    ->when($user?->role === 'agent', fn($q) => $q->where('assigned_user_id', $user->id)),
                'callLogs as total_calls_count' => fn($query) => $query
                    ->when($user?->role === 'agent', fn($q) => $q->where('user_id', $user->id)),
                'callLogs as connected_calls_count' => fn($query) => $query
                    ->whereIn('status', ['connected', 'answered'])
                    ->when($user?->role === 'agent', fn($q) => $q->where('user_id', $user->id)),
                'callLogs as disconnected_calls_count' => fn($query) => $query
                    ->whereIn('status', ['not_connected', 'busy', 'no_answer', 'failed', 'missed'])
                    ->when($user?->role === 'agent', fn($q) => $q->where('user_id', $user->id)),
            ])
            ->when($user?->role === 'agent', function ($query) use ($user) {
                $query->visibleToUser($user->id)
                    ->where(function ($q) {
                        $q->where('status', '!=', 'paused')
                            ->orWhere('hide_paused_from_agents', false);
                    });
            })
            ->when($request->filled('search'), fn($query) => $query->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->filled('pipeline_id'), fn($query) => $query->where('pipeline_id', $request->pipeline_id))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->boolean('pinned'), fn($query) => $query->pinned())
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $campaigns]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCampaign($request);
        $conditionalRules = $this->extractConditionalRules($data);
        $agentIds = $data['agent_ids'] ?? [];
        $agentIds = $this->mergeConditionalAgentIds($agentIds, $conditionalRules, $data['settings']['fallback_user_id'] ?? null);
        unset($data['agent_ids']);

        $campaign = DB::transaction(function () use ($data, $agentIds, $conditionalRules) {
            $campaign = Campaign::create($data);
            $this->syncAgents($campaign, $agentIds);
            $this->storeConditionalRules($campaign, $conditionalRules);

            return $campaign;
        });

        $this->assignmentService->distributeUnassigned($campaign->fresh('users'));

        return response()->json([
            'status' => true,
            'message' => 'Campaign created successfully',
            'data' => $campaign->load(['pipeline', 'manager', 'users']),
        ], 201);
    }

    public function show(Campaign $campaign)
    {
        return response()->json([
            'status' => true,
            'data' => $campaign->load([
                'pipeline.stages.tags',
                'manager:id,name',
                'users' => function ($q) {
                    $q->select('users.id', 'users.name', 'users.email')
                        ->withExists(['crmSessions as is_online' => fn($query) => $query->where('status', 'online')]);
                },
                'contactLists:id,campaign_id,name',
            ]),
        ]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $this->validateCampaign($request);
        $agentIds = $data['agent_ids'] ?? null;
        unset($data['agent_ids']);

        $oldPipelineId = $campaign->pipeline_id;

        DB::transaction(function () use ($campaign, $data, $agentIds, $oldPipelineId) {
            $campaign->update($data);

            if ((int) $oldPipelineId !== (int) $campaign->pipeline_id) {
                $campaign->leads()->update([
                    'pipeline_id' => $campaign->pipeline_id,
                    'stage_id' => null,
                    'tag_id' => null,
                ]);
            }

            if (is_array($agentIds)) {
                $this->syncAgents($campaign, $agentIds);
            }
        });

        $this->assignmentService->distributeUnassigned($campaign->fresh('users'));
        $this->assignmentService->refreshCampaignAgentCounts($campaign);

        return response()->json([
            'status' => true,
            'message' => 'Campaign updated successfully',
            'data' => $campaign->fresh(['pipeline', 'manager', 'users']),
        ]);
    }

    public function updateStatus(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['draft', 'active', 'paused', 'completed', 'archived'])],
        ]);

        $campaign->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Campaign status updated successfully',
            'data' => $campaign->fresh(),
        ]);
    }

    public function pin(Request $request, Campaign $campaign)
    {
        $data = $request->validate(['is_pinned' => ['required', 'boolean']]);
        $campaign->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Campaign pin status updated successfully',
            'data' => $campaign->fresh(),
        ]);
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return response()->json(['status' => true, 'message' => 'Campaign deleted successfully']);
    }

    public function updatePriority(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
        ]);

        $campaign->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Campaign priority updated successfully',
            'data' => $campaign->fresh(),
        ]);
    }

    public function addAgents(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'agent_ids' => ['required', 'array', 'min:1'],
            'agent_ids.*' => ['exists:users,id'],
        ]);

        $this->syncAgents($campaign, $data['agent_ids']);
        $this->assignmentService->distributeUnassigned($campaign->fresh('users'));

        return response()->json([
            'status' => true,
            'message' => 'Agents attached successfully',
            'data' => $campaign->fresh(['pipeline', 'users']),
        ]);
    }

    public function removeAgent(Campaign $campaign, \App\Models\User $user)
    {
        $campaign->users()->detach($user->id);
        Lead::where('campaign_id', $campaign->id)
            ->where('assigned_user_id', $user->id)
            ->update(['assigned_user_id' => null]);
        $this->assignmentService->distributeUnassigned($campaign->fresh('users'));

        return response()->json([
            'status' => true,
            'message' => 'Agent removed successfully',
            'data' => $campaign->fresh(['pipeline', 'users']),
        ]);
    }

    public function assignmentRules(Campaign $campaign)
    {
        return response()->json([
            'status' => true,
            'data' => $campaign->assignmentRules()
                ->with('user:id,name,email,phone_number')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function storeAssignmentRule(Request $request, Campaign $campaign)
    {
        $data = $this->validateAssignmentRule($request, $campaign);
        $rule = $campaign->assignmentRules()->create($data);

        $this->assignmentService->distributeUnassigned($campaign);

        return response()->json([
            'status' => true,
            'message' => 'Assignment rule created successfully',
            'data' => $rule->load('user:id,name,email,phone_number'),
        ], 201);
    }

    public function updateAssignmentRule(Request $request, Campaign $campaign, AssignmentRule $rule)
    {
        abort_if($rule->campaign_id !== $campaign->id, 404);

        $rule->update($this->validateAssignmentRule($request, $campaign, true));
        $this->assignmentService->distributeUnassigned($campaign);

        return response()->json([
            'status' => true,
            'message' => 'Assignment rule updated successfully',
            'data' => $rule->fresh('user:id,name,email,phone_number'),
        ]);
    }

    public function destroyAssignmentRule(Campaign $campaign, AssignmentRule $rule)
    {
        abort_if($rule->campaign_id !== $campaign->id, 404);

        $rule->delete();

        return response()->json([
            'status' => true,
            'message' => 'Assignment rule deleted successfully',
        ]);
    }

    public function leadFunnel(Campaign $campaign)
    {
        $stages = $campaign->pipeline->stages()
            ->withCount(['leads' => fn($q) => $q->where('campaign_id', $campaign->id)])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['status' => true, 'data' => $stages]);
    }

    public function tagsSummary(Campaign $campaign)
    {
        $tags = \App\Models\StageTag::whereHas('leads', fn($q) => $q->where('campaign_id', $campaign->id))
            ->withCount(['leads' => fn($q) => $q->where('campaign_id', $campaign->id)])
            ->orderByDesc('leads_count')
            ->get();

        return response()->json(['status' => true, 'data' => $tags]);
    }

    public function summary(Campaign $campaign)
    {
        $stats = $campaign->leads()
            ->selectRaw('
                count(*) as total_leads,
                sum(case when assigned_user_id is null then 1 else 0 end) as unassigned_leads,
                sum(case when assigned_user_id is not null then 1 else 0 end) as assigned_leads,
                sum(case when status = "uncontacted" then 1 else 0 end) as uncontacted_leads,
                sum(case when status = "in_progress" then 1 else 0 end) as in_progress_leads,
                sum(case when status = "in_progress" and next_follow_up_at is null then 1 else 0 end) as no_follow_up_leads,
                sum(case when status = "in_progress" and next_follow_up_at is not null then 1 else 0 end) as follow_up_leads,
                sum(case when status = "converted" then 1 else 0 end) as converted_leads,
                sum(case when status = "lost" then 1 else 0 end) as lost_leads,
                sum(case when status = "closed" then 1 else 0 end) as closed_by_system
            ')
            ->first();

        return response()->json([
            'status' => true,
            'data' => [
                'campaign' => $campaign,
                'total_leads' => (int) ($stats->total_leads ?? 0),
                'unassigned_leads' => (int) ($stats->unassigned_leads ?? 0),
                'assigned_leads' => (int) ($stats->assigned_leads ?? 0),
                'uncontacted_leads' => (int) ($stats->uncontacted_leads ?? 0),
                'in_progress_leads' => (int) ($stats->in_progress_leads ?? 0),
                'no_follow_up_leads' => (int) ($stats->no_follow_up_leads ?? 0),
                'follow_up_leads' => (int) ($stats->follow_up_leads ?? 0),
                'converted_leads' => (int) ($stats->converted_leads ?? 0),
                'lost_leads' => (int) ($stats->lost_leads ?? 0),
                'closed_by_system' => (int) ($stats->closed_by_system ?? 0),
            ],
        ]);
    }

    private function validateCampaign(Request $request): array
    {
        return $request->validate([
            'pipeline_id' => ['required', 'exists:pipelines,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'paused', 'completed', 'archived'])],
            'distribution' => ['sometimes', Rule::in(['on_demand', 'equal', 'conditional', 'auto_assign'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'is_pinned' => ['sometimes', 'boolean'],
            'hide_paused_from_agents' => ['sometimes', 'boolean'],
            'lead_chunk_size' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'settings' => ['nullable', 'array'],
            'agent_ids' => ['sometimes', 'array'],
            'agent_ids.*' => ['exists:users,id'],
        ]);
    }

    private function extractConditionalRules(array &$data): array
    {
        $rules = $data['settings']['conditional_rules'] ?? [];
        unset($data['settings']['conditional_rules']);

        if (($data['settings'] ?? null) === []) {
            $data['settings'] = null;
        }

        return is_array($rules) ? array_values($rules) : [];
    }

    private function storeConditionalRules(Campaign $campaign, array $rules): void
    {
        if ($campaign->distribution !== 'conditional' || $rules === []) {
            return;
        }

        $agentIds = $campaign->users()
            ->wherePivot('role', 'agent')
            ->pluck('users.id')
            ->map(fn($id) => (int) $id)
            ->all();

        foreach ($rules as $index => $rule) {
            $userId = (int) ($rule['user_id'] ?? 0);
            $field = trim((string) ($rule['condition_field'] ?? $rule['field'] ?? ''));
            $value = trim((string) ($rule['condition_value'] ?? $rule['value'] ?? ''));

            if (!in_array($userId, $agentIds, true)) {
                throw ValidationException::withMessages([
                    'settings.conditional_rules.' . $index . '.user_id' => 'Selected user must be an agent on this campaign.',
                ]);
            }

            if ($field === '' || $value === '') {
                throw ValidationException::withMessages([
                    'settings.conditional_rules.' . $index . '.condition_value' => 'Condition field and value are required.',
                ]);
            }

            $campaign->assignmentRules()->create([
                'name' => $rule['name'] ?? 'Condition ' . ($index + 1),
                'user_id' => $userId,
                'condition_field' => $field,
                'condition_operator' => (string) ($rule['condition_operator'] ?? $rule['operator'] ?? 'contains'),
                'condition_value' => $value,
                'sort_order' => (int) ($rule['sort_order'] ?? ($index + 1)),
                'is_active' => (bool) ($rule['is_active'] ?? true),
                'settings' => $rule['settings'] ?? null,
            ]);
        }
    }

    private function mergeConditionalAgentIds(array $agentIds, array $rules, mixed $fallbackUserId): array
    {
        $ids = collect($agentIds);

        foreach ($rules as $rule) {
            if (!empty($rule['user_id'])) {
                $ids->push((int) $rule['user_id']);
            }
        }

        if ($fallbackUserId) {
            $ids->push((int) $fallbackUserId);
        }

        return $ids->filter()->unique()->values()->all();
    }

    private function syncAgents(Campaign $campaign, array $agentIds): void
    {
        $sync = [];

        foreach ($agentIds as $agentId) {
            $sync[$agentId] = ['role' => 'agent', 'is_active' => true];
        }

        if ($campaign->manager_id) {
            $sync[$campaign->manager_id] = ['role' => 'manager', 'is_active' => true];
        }

        $campaign->users()->sync($sync);

        $orphanedLeads = Lead::where('campaign_id', $campaign->id)->whereNotNull('assigned_user_id');
        if ($sync !== []) {
            $orphanedLeads->whereNotIn('assigned_user_id', array_keys($sync));
        }
        $orphanedLeads->update(['assigned_user_id' => null]);
    }

    private function validateAssignmentRule(Request $request, Campaign $campaign, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'user_id' => [
                $required,
                Rule::exists('campaign_user', 'user_id')->where('campaign_id', $campaign->id),
            ],
            'name' => ['nullable', 'string', 'max:120'],
            'condition_field' => [$required, 'string', 'max:120'],
            'condition_operator' => ['sometimes', Rule::in(['equals', 'not_equals', 'contains', 'starts_with', 'ends_with'])],
            'condition_value' => [$required, 'string', 'max:180'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);
    }
}
