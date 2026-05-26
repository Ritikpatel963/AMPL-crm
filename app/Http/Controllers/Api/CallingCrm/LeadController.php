<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CrmNote;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\LeadPhoneNumber;
use App\Models\LeadStage;
use App\Models\StageTag;
use App\Models\TimelineEvent;
use App\Services\CallingCrm\LeadAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function __construct(private LeadAssignmentService $assignmentService) {}

    public function index(Request $request)
    {
        $leads = Lead::query()
            ->select([
                'id', 'campaign_id', 'pipeline_id', 'assigned_user_id', 'stage_id',
                'tag_id', 'source_id', 'name', 'phone', 'email', 'source', 'status',
                'priority_bucket', 'deal_amount', 'currency', 'last_call_at',
                'next_follow_up_at', 'total_disposition_count', 'metadata', 'created_at', 'updated_at',
            ])
            ->with([
                'campaign:id,name,status,pipeline_id',
                'campaign.pipeline:id,name',
                'pipeline:id,name',
                'stage:id,name,color,category',
                'tag:id,name,color',
                'assignedUser:id,name,phone_number',
                'leadSource:id,name,code',
                'phoneNumbers:id,lead_id,phone,type,is_primary',
                'latestCall:call_logs.id,call_logs.lead_id,call_logs.user_id,call_logs.status,call_logs.direction,call_logs.phone_number,call_logs.duration_seconds,call_logs.ring_duration_seconds,call_logs.recording_url,call_logs.called_at,call_logs.started_at,call_logs.answered_at,call_logs.ended_at,call_logs.created_at',
                'latestCall.user:id,name,phone_number',
                'propertyValues' => fn ($query) => $query->select('id', 'lead_id', 'property_id', 'value')->with('property:id,name,slug,data_type'),
            ])
            ->withCount('callLogs')
            ->withSum('callLogs as total_call_duration_seconds', 'duration_seconds')
            ->when($request->user()?->role === 'agent', fn ($query) => $this->applyAgentLeadVisibility($query, $request->user()))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->search . '%';
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->when($request->filled('pipeline_id'), fn ($query) => $query->where('pipeline_id', $request->pipeline_id))
            ->when($request->filled('stage_id'), fn ($query) => $query->where('stage_id', $request->stage_id))
            ->when($request->filled('tag_id'), fn ($query) => $query->where('tag_id', $request->tag_id))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->source))
            ->when($request->filled('assigned_user_id'), fn ($query) => $query->where('assigned_user_id', $request->assigned_user_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        $leads->getCollection()->transform(fn (Lead $lead) => $this->leadListPayload($lead));

        return response()->json(['status' => true, 'data' => $leads]);
    }

    public function store(Request $request)
    {
        // Fix for frontend sending name and phone backwards
        if ($request->has('name') && $request->has('phone')) {
            $name = $request->input('name');
            $phone = $request->input('phone');
            if (preg_match('/^[0-9\+\-\(\)\s]+$/', $name) && preg_match('/[a-zA-Z]/', $phone)) {
                $request->merge([
                    'name' => $phone,
                    'phone' => $name,
                ]);
            }
        }

        $data = $this->validateLead($request);
        $properties = $data['properties'] ?? [];
        unset($data['properties']);

        $lead = DB::transaction(function () use ($data, $properties) {
            $data = $this->applyCampaignPipeline($data);
            $lead = Lead::create($data);
            $this->syncProperties($lead, $properties);
            $this->assignmentService->assignLead($lead);

            return $lead;
        });

        return response()->json([
            'status' => true,
            'message' => 'Lead created successfully',
            'data' => $lead->load([
                'campaign:id,name,pipeline_id',
                'campaign.pipeline:id,name',
                'stage:id,name,color',
                'tag:id,name,color',
                'assignedUser:id,name',
                'pipeline:id,name',
                'propertyValues.property:id,name,slug,data_type',
            ]),
        ], 201);
    }

    public function show(Lead $lead)
    {
        abort_if(! $this->canAccessLead(request()->user(), $lead), 403);

        return response()->json([
            'status' => true,
            'data' => $lead->load([
                'campaign:id,name,status',
                'campaign.pipeline:id,name',
                'pipeline:id,name',
                'stage:id,name,color,category',
                'tag:id,name,color',
                'assignedUser:id,name,phone_number,email',
                'leadSource:id,name,code',
                'contactList:id,file_name,sheet_name,status,processed_at',
                'phoneNumbers:id,lead_id,phone,type,is_primary',
                'propertyValues' => fn ($query) => $query->select('id', 'lead_id', 'property_id', 'value', 'value_text', 'value_number', 'value_date', 'value_json')->with('property:id,name,slug,data_type'),
                'callLogs' => fn ($query) => $query
                    ->select('id', 'lead_id', 'campaign_id', 'user_id', 'disposition_id', 'status', 'direction', 'provider_call_id', 'phone_number', 'duration_seconds', 'ring_duration_seconds', 'recording_url', 'notes', 'called_at', 'started_at', 'answered_at', 'ended_at', 'created_at')
                    ->with(['user:id,name,phone_number', 'campaign:id,name', 'disposition:id,name'])
                    ->latest('started_at')
                    ->limit(50),
                'dispositions' => fn ($query) => $query
                    ->select('id', 'lead_id', 'call_log_id', 'user_id', 'campaign_id', 'from_stage_id', 'to_stage_id', 'tag_id', 'disposition_id', 'call_status', 'remark', 'disposed_at', 'created_at')
                    ->with(['disposition:id,name', 'user:id,name', 'toStage:id,name,color,category', 'tag:id,name,color'])
                    ->latest('disposed_at')
                    ->limit(50),
                'followUps' => fn ($query) => $query->select('id', 'lead_id', 'user_id', 'scheduled_at', 'status', 'note')->latest('scheduled_at')->limit(20),
                'notes' => fn ($query) => $query->select('id', 'lead_id', 'user_id', 'note', 'visibility', 'created_at')->latest()->limit(20),
                'timelineEvents' => fn ($query) => $query
                    ->select('id', 'lead_id', 'user_id', 'event_type', 'title', 'description', 'payload', 'occurred_at')
                    ->with('user:id,name')
                    ->limit(50),
            ]),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $this->validateLead($request, true, $lead);
        $properties = $data['properties'] ?? null;
        unset($data['properties']);

        DB::transaction(function () use ($lead, $data, $properties) {
            $data = $this->applyCampaignPipeline($data, $lead);
            $lead->update($data);

            if (is_array($properties)) {
                $this->syncProperties($lead, $properties);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Lead updated successfully',
            'data' => $lead->fresh([
                'campaign:id,name',
                'campaign.pipeline:id,name',
                'pipeline:id,name',
                'stage:id,name,color',
                'tag:id,name,color',
                'assignedUser:id,name',
                'propertyValues.property:id,name,slug,data_type',
            ]),
        ]);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return response()->json(['status' => true, 'message' => 'Lead deleted successfully']);
    }

    public function reassign(Request $request)
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['exists:leads,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
        ]);

        $affectedCampaignIds = Lead::whereIn('id', $data['lead_ids'])
            ->pluck('campaign_id')
            ->filter()
            ->all();

        $updates = [];

        if ($request->has('assigned_user_id')) {
            $updates['assigned_user_id'] = $data['assigned_user_id'] ?? null;
        }

        if ($request->filled('campaign_id')) {
            $updates['campaign_id'] = $data['campaign_id'];
            $updates['pipeline_id'] = Campaign::whereKey($data['campaign_id'])->value('pipeline_id');
            $updates['stage_id'] = null;
            $updates['tag_id'] = null;
            $affectedCampaignIds[] = (int) $data['campaign_id'];
        }

        if (array_key_exists('assigned_user_id', $updates)) {
            $this->assertAssignableUserForLeads(
                $data['lead_ids'],
                $updates['assigned_user_id'],
                $updates['campaign_id'] ?? null
            );
        }

        if ($updates !== []) {
            Lead::whereIn('id', $data['lead_ids'])->update($updates);
        }

        $this->refreshAssignmentCounts($affectedCampaignIds);

        return response()->json([
            'status' => true,
            'message' => 'Leads reassigned successfully',
            'data' => ['updated' => count($data['lead_ids'])],
        ]);
    }

    public function timeline(Lead $lead)
    {
        abort_if(! $this->canAccessLead(request()->user(), $lead), 403);

        return response()->json([
            'status' => true,
            'data' => $lead->timelineEvents()
                ->with('user:id,name')
                ->latest('occurred_at')
                ->paginate(request()->integer('per_page', 50)),
        ]);
    }

    public function history(Lead $lead)
    {
        abort_if(! $this->canAccessLead(request()->user(), $lead), 403);

        return response()->json([
            'status' => true,
            'data' => $lead->dispositions()
                ->with(['disposition:id,name', 'user:id,name', 'toStage:id,name', 'tag:id,name'])
                ->latest('disposed_at')
                ->paginate(request()->integer('per_page', 50)),
        ]);
    }

    public function storeNote(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'note' => ['required', 'string'],
            'visibility' => ['sometimes', 'in:private,team,manager'],
            'is_confidential' => ['sometimes', 'boolean'],
        ]);

        $note = $lead->notes()->create([
            'user_id' => auth()->id(),
            'note' => $data['note'],
            'visibility' => $data['visibility'] ?? 'team',
            'is_confidential' => $data['is_confidential'] ?? false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Note added successfully',
            'data' => $note,
        ], 201);
    }

    public function storePhoneNumber(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20', Rule::unique('lead_phone_numbers', 'phone')->where('lead_id', $lead->id)],
            'type' => ['sometimes', 'in:primary,alternate,whatsapp'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $phoneNumber = $lead->phoneNumbers()->create($data);

        return response()->json([
            'status' => true,
            'message' => 'Phone number added successfully',
            'data' => $phoneNumber,
        ], 201);
    }

    public function claimNext(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'chunk_size' => ['sometimes', 'integer', 'min:1', 'max:25'],
        ]);

        $campaign = Campaign::findOrFail($data['campaign_id']);
        $chunkSize = $data['chunk_size'] ?? ($campaign->lead_chunk_size ?? 10);
        $leads = $this->assignmentService->claimNextForUser($campaign, $request->user(), $chunkSize);

        return response()->json([
            'status' => true,
            'message' => $leads->isNotEmpty() ? 'Leads assigned successfully' : 'No unassigned leads available',
            'data' => $leads,
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['exists:leads,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'stage_id' => ['nullable', 'exists:lead_stages,id'],
            'tag_id' => ['nullable', 'exists:stage_tags,id'],
            'status' => ['sometimes', Rule::in(['uncontacted', 'in_progress', 'converted', 'lost', 'closed', 'reopened'])],
        ]);

        $updates = [];

        foreach (['assigned_user_id', 'stage_id', 'tag_id', 'status'] as $field) {
            if ($request->has($field)) {
                $updates[$field] = $data[$field] ?? null;
            }
        }

        $affectedCampaignIds = Lead::whereIn('id', $data['lead_ids'])
            ->pluck('campaign_id')
            ->filter()
            ->all();

        if (array_key_exists('assigned_user_id', $updates)) {
            $this->assertAssignableUserForLeads($data['lead_ids'], $updates['assigned_user_id']);
        }

        if ($updates !== []) {
            Lead::whereIn('id', $data['lead_ids'])->update($updates);
        }

        if (array_key_exists('assigned_user_id', $updates)) {
            $this->refreshAssignmentCounts($affectedCampaignIds);
        }

        return response()->json([
            'status' => true,
            'message' => 'Leads updated successfully',
            'data' => ['updated' => count($data['lead_ids'])],
        ]);
    }

    public function bulkMove(Request $request)
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['exists:leads,id'],
            'campaign_id' => ['required', 'exists:campaigns,id'],
        ]);

        $campaign = Campaign::findOrFail($data['campaign_id']);
        $affectedCampaignIds = Lead::whereIn('id', $data['lead_ids'])
            ->pluck('campaign_id')
            ->filter()
            ->all();

        Lead::whereIn('id', $data['lead_ids'])
            ->update([
                'campaign_id' => $campaign->id,
                'pipeline_id' => $campaign->pipeline_id,
                'stage_id' => null,
                'tag_id' => null,
                'assigned_user_id' => null,
            ]);

        $this->assignmentService->distributeUnassigned($campaign->fresh('users'));
        $this->refreshAssignmentCounts(array_merge($affectedCampaignIds, [$campaign->id]));

        return response()->json([
            'status' => true,
            'message' => 'Leads moved successfully',
            'data' => ['moved' => count($data['lead_ids'])],
        ]);
    }

    public function bulkCopy(Request $request)
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['exists:leads,id'],
            'campaign_id' => ['required', 'exists:campaigns,id'],
        ]);

        $campaign = Campaign::findOrFail($data['campaign_id']);

        $leads = Lead::whereIn('id', $data['lead_ids'])->get();

        $copied = 0;
        foreach ($leads as $lead) {
            $newLead = $lead->replicate();
            $newLead->campaign_id = $campaign->id;
            $newLead->pipeline_id = $campaign->pipeline_id;
            $newLead->stage_id = null;
            $newLead->tag_id = null;
            $newLead->assigned_user_id = null;
            $newLead->save();
            $this->assignmentService->assignLead($newLead);
            $copied++;
        }

        $this->assignmentService->refreshCampaignAgentCounts($campaign);

        return response()->json([
            'status' => true,
            'message' => 'Leads copied successfully',
            'data' => ['copied' => $copied],
        ], 201);
    }

    public function bulkClose(Request $request)
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['exists:leads,id'],
            'stage_id' => ['nullable', 'exists:lead_stages,id'],
            'tag_id' => ['nullable', 'exists:stage_tags,id'],
        ]);

        Lead::whereIn('id', $data['lead_ids'])
            ->update([
                'status' => 'closed',
                'stage_id' => $data['stage_id'] ?? null,
                'tag_id' => $data['tag_id'] ?? null,
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Leads closed successfully',
            'data' => ['closed' => count($data['lead_ids'])],
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['exists:leads,id'],
        ]);

        $affectedCampaignIds = Lead::whereIn('id', $data['lead_ids'])
            ->pluck('campaign_id')
            ->filter()
            ->all();

        Lead::whereIn('id', $data['lead_ids'])->delete();
        $this->refreshAssignmentCounts($affectedCampaignIds);

        return response()->json([
            'status' => true,
            'message' => 'Leads deleted successfully',
            'data' => ['deleted' => count($data['lead_ids'])],
        ]);
    }

    private function validateLead(Request $request, bool $partial = false, ?Lead $lead = null): array
    {
        $required = $partial ? 'sometimes' : 'required';

        if ($request->has('user_id') && $request->input('user_id') !== null) {
            $exists = \App\Models\User::where('id', $request->input('user_id'))->exists();
            if (!$exists) {
                $request->merge(['user_id' => null]);
            }
        }

        $data = $request->validate([
            'campaign_id' => [$required, 'exists:campaigns,id'],
            'pipeline_id' => ['nullable', 'exists:pipelines,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'stage_id' => ['nullable', 'exists:lead_stages,id'],
            'tag_id' => ['nullable', 'exists:stage_tags,id'],
            'source_id' => ['nullable', 'exists:lead_sources,id'],
            'contact_list_id' => ['nullable', 'exists:contact_lists,id'],
            'name' => ['nullable', 'string', 'max:180'],
            'phone' => [$required, 'string', 'max:20', Rule::unique('leads', 'phone')->ignore($lead?->id)],
            'email' => ['nullable', 'email', 'max:180'],
            'source' => ['sometimes', Rule::in(['FILE_UPLOAD', 'WALK_IN_LEAD', 'INCOMING_IVR', 'WORKFLOW', 'GOOGLE_SHEET', 'MANUAL', 'API', 'WEBHOOK'])],
            'tags' => ['nullable', 'array'],
            'status' => ['sometimes', Rule::in(['uncontacted', 'in_progress', 'converted', 'lost', 'closed', 'reopened'])],
            'priority_bucket' => ['sometimes', Rule::in(['manual_scheduled', 'assigned_uncontacted', 'unassigned_uncontacted', 'in_progress_no_followup', 'not_connected_scheduled', 'normal'])],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'last_call_at' => ['nullable', 'date'],
            'next_follow_up_at' => ['nullable', 'date'],
            'confidential_remark' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'properties' => ['nullable', 'array'],
        ]);

        return $this->validateLeadPipelineState($data, $lead);
    }

    private function syncProperties(Lead $lead, array $properties): void
    {
        foreach ($properties as $propertyId => $value) {
            $lead->propertyValues()->updateOrCreate(
                ['property_id' => $propertyId],
                [
                    'value' => is_scalar($value) ? (string) $value : null,
                    'value_text' => is_scalar($value) ? (string) $value : null,
                    'value_number' => is_numeric($value) ? $value : null,
                    'value_json' => is_array($value) ? $value : null,
                ]
            );
        }
    }

    private function canAccessLead($user, Lead $lead): bool
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

        return false;
    }

    private function applyAgentLeadVisibility($query, $user)
    {
        return $query->where(function ($visible) use ($user) {
            $visible->where('assigned_user_id', $user->id)
                ->orWhere(function ($unassigned) use ($user) {
                    $unassigned->whereNull('assigned_user_id')
                        ->whereHas('campaign', function ($campaign) use ($user) {
                            $campaign->visibleToUser($user->id)
                                ->where(function ($query) {
                                    $query->where('status', '!=', 'paused')
                                        ->orWhere('hide_paused_from_agents', false);
                                });
                        });
                });
        });
    }

    private function leadListPayload(Lead $lead): array
    {
        $latestCall = $lead->latestCall;

        return [
            'id' => $lead->id,
            'campaign_id' => $lead->campaign_id,
            'pipeline_id' => $lead->pipeline_id,
            'assigned_user_id' => $lead->assigned_user_id,
            'stage_id' => $lead->stage_id,
            'tag_id' => $lead->tag_id,
            'source_id' => $lead->source_id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'source' => $lead->source,
            'status' => $lead->status,
            'priority_bucket' => $lead->priority_bucket,
            'deal_amount' => $lead->deal_amount,
            'currency' => $lead->currency,
            'last_call_at' => $lead->last_call_at,
            'next_follow_up_at' => $lead->next_follow_up_at,
            'total_disposition_count' => $lead->total_disposition_count,
            'metadata' => $lead->metadata,
            'created_at' => $lead->created_at,
            'updated_at' => $lead->updated_at,
            'campaign' => $lead->campaign,
            'pipeline' => $lead->pipeline ?: $lead->campaign?->pipeline,
            'campaign_pipeline' => $lead->campaign?->pipeline,
            'stage' => $lead->stage,
            'tag' => $lead->tag,
            'assigned_user' => $lead->assignedUser,
            'lead_source' => $lead->leadSource,
            'phone_numbers' => $lead->phoneNumbers,
            'property_values' => $lead->propertyValues,
            'call_summary' => [
                'total_calls' => (int) ($lead->call_logs_count ?? 0),
                'total_duration_seconds' => (int) ($lead->total_call_duration_seconds ?? 0),
                'last_call_at' => $lead->last_call_at,
                'latest_status' => $latestCall?->status,
                'latest_duration_seconds' => $latestCall?->duration_seconds,
                'latest_recording_url' => $latestCall?->recording_url,
            ],
            'latest_call' => $latestCall,
        ];
    }

    private function applyCampaignPipeline(array $data, ?Lead $lead = null): array
    {
        $campaignId = $data['campaign_id'] ?? $lead?->campaign_id;

        if ($campaignId) {
            $data['pipeline_id'] = Campaign::whereKey($campaignId)->value('pipeline_id');
        }

        return $data;
    }

    private function validateLeadPipelineState(array $data, ?Lead $lead = null): array
    {
        $campaignId = $data['campaign_id'] ?? $lead?->campaign_id;
        $pipelineId = $data['pipeline_id'] ?? null;

        if ($campaignId) {
            $pipelineId = Campaign::whereKey($campaignId)->value('pipeline_id');
        }

        if (isset($data['stage_id']) && $data['stage_id'] && $pipelineId) {
            abort_unless(
                LeadStage::whereKey($data['stage_id'])->where('pipeline_id', $pipelineId)->exists(),
                422,
                'Selected stage does not belong to the lead pipeline.'
            );
        }

        if (isset($data['tag_id']) && $data['tag_id']) {
            $tagQuery = StageTag::whereKey($data['tag_id']);

            if ($pipelineId) {
                $tagQuery->whereHas('stage', fn ($query) => $query->where('pipeline_id', $pipelineId));
            }

            abort_unless($tagQuery->exists(), 422, 'Selected tag does not belong to the lead pipeline.');
        }

        if (isset($data['assigned_user_id']) && $data['assigned_user_id'] && $campaignId) {
            abort_unless(
                $this->campaignCanAssignUser((int) $campaignId, (int) $data['assigned_user_id']),
                422,
                'Selected user is not an active agent on the selected campaign.'
            );
        }

        return $data;
    }

    private function refreshAssignmentCounts(array $campaignIds): void
    {
        Campaign::whereIn('id', array_unique(array_filter($campaignIds)))
            ->get()
            ->each(fn (Campaign $campaign) => $this->assignmentService->refreshCampaignAgentCounts($campaign));
    }

    private function assertAssignableUserForLeads(array $leadIds, ?int $userId, ?int $targetCampaignId = null): void
    {
        if (! $userId) {
            return;
        }

        $campaignIds = $targetCampaignId
            ? collect([(int) $targetCampaignId])
            : Lead::whereIn('id', $leadIds)->pluck('campaign_id')->unique()->values();

        $validCampaignCount = Campaign::whereIn('id', $campaignIds)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId)
                    ->whereIn('users.role', ['agent', 'subadmin'])
                    ->where(function ($active) {
                        $active->where('campaign_user.is_active', true)
                            ->orWhereNull('campaign_user.is_active');
                    })
                    ->where(function ($role) {
                        $role->where('campaign_user.role', 'agent')
                            ->orWhereNull('campaign_user.role');
                    });
            })
            ->count();

        abort_unless(
            $validCampaignCount === $campaignIds->count(),
            422,
            'Selected user is not an active agent on every selected campaign.'
        );
    }

    private function campaignCanAssignUser(int $campaignId, int $userId): bool
    {
        return Campaign::whereKey($campaignId)
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId)
                    ->whereIn('users.role', ['agent', 'subadmin'])
                    ->where(function ($active) {
                        $active->where('campaign_user.is_active', true)
                            ->orWhereNull('campaign_user.is_active');
                    })
                    ->where(function ($role) {
                        $role->where('campaign_user.role', 'agent')
                            ->orWhereNull('campaign_user.role');
                    });
            })
            ->exists();
    }
}
