<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CrmNote;
use App\Models\Lead;
use App\Models\LeadPhoneNumber;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::query()
            ->select([
                'id', 'campaign_id', 'pipeline_id', 'assigned_user_id', 'stage_id',
                'tag_id', 'source_id', 'name', 'phone', 'email', 'source', 'status',
                'priority_bucket', 'deal_amount', 'currency', 'last_call_at',
                'next_follow_up_at', 'total_disposition_count', 'created_at', 'updated_at',
            ])
            ->with([
                'campaign:id,name,status',
                'pipeline:id,name',
                'stage:id,name,color,category',
                'tag:id,name,color',
                'assignedUser:id,name,phone_number',
                'leadSource:id,name,code',
                'propertyValues' => fn ($query) => $query->select('id', 'lead_id', 'property_id', 'value')->with('property:id,name,slug,data_type'),
            ])
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

        return response()->json(['status' => true, 'data' => $leads]);
    }

    public function store(Request $request)
    {
        $data = $this->validateLead($request);
        $properties = $data['properties'] ?? [];
        unset($data['properties']);

        $lead = DB::transaction(function () use ($data, $properties) {
            $lead = Lead::create($data);
            $this->syncProperties($lead, $properties);

            return $lead;
        });

        return response()->json([
            'status' => true,
            'message' => 'Lead created successfully',
            'data' => $lead->load([
                'campaign:id,name',
                'stage:id,name,color',
                'tag:id,name,color',
                'assignedUser:id,name',
                'propertyValues.property:id,name,slug,data_type',
            ]),
        ], 201);
    }

    public function show(Lead $lead)
    {
        return response()->json([
            'status' => true,
            'data' => $lead->load([
                'campaign:id,name,status',
                'stage:id,name,color,category',
                'tag:id,name,color',
                'assignedUser:id,name,phone_number,email',
                'leadSource:id,name,code',
                'phoneNumbers:id,lead_id,phone,type,is_primary',
                'propertyValues' => fn ($query) => $query->select('id', 'lead_id', 'property_id', 'value', 'value_text', 'value_number')->with('property:id,name,slug,data_type'),
                'callLogs' => fn ($query) => $query->select('id', 'lead_id', 'user_id', 'status', 'direction', 'started_at', 'duration_seconds')->latest('started_at')->limit(50),
                'followUps' => fn ($query) => $query->select('id', 'lead_id', 'user_id', 'scheduled_at', 'status', 'note')->latest('scheduled_at')->limit(20),
                'notes' => fn ($query) => $query->select('id', 'lead_id', 'user_id', 'note', 'visibility', 'created_at')->latest()->limit(20),
                'timelineEvents' => fn ($query) => $query->select('id', 'lead_id', 'user_id', 'event_type', 'title', 'description', 'occurred_at')->limit(50),
            ]),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $this->validateLead($request, true);
        $properties = $data['properties'] ?? null;
        unset($data['properties']);

        DB::transaction(function () use ($lead, $data, $properties) {
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

        $updates = array_filter([
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
        ], fn ($value) => $value !== null);

        Lead::whereIn('id', $data['lead_ids'])->update($updates);

        return response()->json([
            'status' => true,
            'message' => 'Leads reassigned successfully',
            'data' => ['updated' => count($data['lead_ids'])],
        ]);
    }

    public function timeline(Lead $lead)
    {
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

        $campaign = \App\Models\Campaign::findOrFail($data['campaign_id']);
        $chunkSize = $data['chunk_size'] ?? ($campaign->lead_chunk_size ?? 10);
        $userId = auth()->id();

        $leads = Lead::where('campaign_id', $campaign->id)
            ->whereNull('assigned_user_id')
            ->where('status', 'uncontacted')
            ->orderBy('created_at')
            ->limit($chunkSize)
            ->get();

        $leadIds = $leads->pluck('id');

        if ($leadIds->isNotEmpty()) {
            Lead::whereIn('id', $leadIds)->update(['assigned_user_id' => $userId]);
        }

        return response()->json([
            'status' => true,
            'message' => $leadIds->isNotEmpty() ? 'Leads assigned successfully' : 'No unassigned leads available',
            'data' => Lead::whereIn('id', $leadIds)
                ->with(['campaign:id,name', 'stage:id,name,color', 'tag:id,name,color'])
                ->get(),
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

        $updates = array_filter([
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'stage_id' => $data['stage_id'] ?? null,
            'tag_id' => $data['tag_id'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn ($v) => $v !== null);

        Lead::whereIn('id', $data['lead_ids'])->update($updates);

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

        $campaign = \App\Models\Campaign::findOrFail($data['campaign_id']);

        Lead::whereIn('id', $data['lead_ids'])
            ->update([
                'campaign_id' => $campaign->id,
                'pipeline_id' => $campaign->pipeline_id,
            ]);

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

        $campaign = \App\Models\Campaign::findOrFail($data['campaign_id']);

        $leads = Lead::whereIn('id', $data['lead_ids'])->get();

        $copied = 0;
        foreach ($leads as $lead) {
            $newLead = $lead->replicate();
            $newLead->campaign_id = $campaign->id;
            $newLead->pipeline_id = $campaign->pipeline_id;
            $newLead->save();
            $copied++;
        }

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

    private function validateLead(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        if ($request->has('user_id') && $request->input('user_id') !== null) {
            $exists = \App\Models\User::where('id', $request->input('user_id'))->exists();
            if (!$exists) {
                $request->merge(['user_id' => null]);
            }
        }

        return $request->validate([
            'campaign_id' => [$required, 'exists:campaigns,id'],
            'pipeline_id' => ['nullable', 'exists:pipelines,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'stage_id' => ['nullable', 'exists:lead_stages,id'],
            'tag_id' => ['nullable', 'exists:stage_tags,id'],
            'source_id' => ['nullable', 'exists:lead_sources,id'],
            'contact_list_id' => ['nullable', 'exists:contact_lists,id'],
            'name' => ['nullable', 'string', 'max:180'],
            'phone' => [$required, 'string', 'max:20'],
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
}
