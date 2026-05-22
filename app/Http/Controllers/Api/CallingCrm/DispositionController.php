<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\Disposition;
use App\Models\Lead;
use App\Models\LeadDisposition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DispositionController extends Controller
{
    public function index(Request $request)
    {
        $dispositions = Disposition::with(['pipeline', 'stage', 'tag'])
            ->when($request->filled('pipeline_id'), fn ($query) => $query->where('pipeline_id', $request->pipeline_id))
            ->when($request->boolean('active_only'), fn ($query) => $query->active())
            ->orderBy('sort_order')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $dispositions]);
    }

    public function store(Request $request)
    {
        $disposition = Disposition::create($this->validateDisposition($request));

        return response()->json([
            'status' => true,
            'message' => 'Disposition created successfully',
            'data' => $disposition,
        ], 201);
    }

    public function update(Request $request, Disposition $disposition)
    {
        $disposition->update($this->validateDisposition($request));

        return response()->json([
            'status' => true,
            'message' => 'Disposition updated successfully',
            'data' => $disposition->fresh(['pipeline', 'stage', 'tag']),
        ]);
    }

    public function destroy(Disposition $disposition)
    {
        $disposition->delete();

        return response()->json(['status' => true, 'message' => 'Disposition deleted successfully']);
    }

    public function disposeLead(Request $request, Lead $lead)
    {
        $data = $request->validate([
            'call_log_id' => ['nullable', 'exists:call_logs,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'disposition_id' => ['nullable', 'exists:dispositions,id'],
            'stage_id' => ['nullable', 'exists:lead_stages,id'],
            'tag_id' => ['nullable', 'exists:stage_tags,id'],
            'call_status' => ['required', Rule::in(['connected', 'not_connected', 'missed', 'busy', 'no_answer', 'failed'])],
            'remark' => ['nullable', 'string'],
            'follow_up_at' => ['nullable', 'date'],
            'deal_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $leadDisposition = DB::transaction(function () use ($lead, $data) {
            $leadDisposition = LeadDisposition::create([
                'lead_id' => $lead->id,
                'call_log_id' => $data['call_log_id'] ?? null,
                'user_id' => $data['user_id'] ?? auth()->id() ?? $lead->assigned_user_id ?? $lead->user_id,
                'campaign_id' => $data['campaign_id'] ?? $lead->campaign_id,
                'from_stage_id' => $lead->stage_id,
                'to_stage_id' => $data['stage_id'] ?? $lead->stage_id,
                'tag_id' => $data['tag_id'] ?? $lead->tag_id,
                'disposition_id' => $data['disposition_id'] ?? null,
                'call_status' => $data['call_status'],
                'remark' => $data['remark'] ?? null,
                'disposed_at' => now(),
            ]);

            $leadUpdates = [
                'stage_id' => $data['stage_id'] ?? $lead->stage_id,
                'tag_id' => $data['tag_id'] ?? $lead->tag_id,
                'total_disposition_count' => $lead->total_disposition_count + 1,
            ];

            if (isset($data['deal_amount'])) {
                $leadUpdates['deal_amount'] = $data['deal_amount'];
            }

            if (isset($data['follow_up_at'])) {
                $leadUpdates['next_follow_up_at'] = $data['follow_up_at'];
                $lead->followUps()->create([
                    'campaign_id' => $lead->campaign_id,
                    'user_id' => $lead->assigned_user_id,
                    'created_by' => auth()->id(),
                    'call_log_id' => $data['call_log_id'] ?? null,
                    'scheduled_at' => $data['follow_up_at'],
                    'status' => 'scheduled',
                    'note' => $data['remark'] ?? null,
                ]);
            }

            if (in_array($data['call_status'], ['connected'], true)) {
                $leadUpdates['status'] = 'in_progress';
            }

            $lead->update($leadUpdates);

            $lead->timelineEvents()->create([
                'user_id' => auth()->id(),
                'event_type' => 'lead_disposed',
                'title' => 'Lead Disposed',
                'description' => $data['remark'] ?? null,
                'payload' => $data,
                'occurred_at' => now(),
            ]);

            return $leadDisposition;
        });

        return response()->json([
            'status' => true,
            'message' => 'Lead disposed successfully',
            'data' => $leadDisposition->load(['lead', 'disposition', 'toStage', 'tag']),
        ], 201);
    }

    private function validateDisposition(Request $request): array
    {
        return $request->validate([
            'pipeline_id' => ['required', 'exists:pipelines,id'],
            'stage_id' => ['nullable', 'exists:lead_stages,id'],
            'tag_id' => ['nullable', 'exists:stage_tags,id'],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(['fresh', 'in_progress', 'closed_won', 'closed_lost', 'not_connected'])],
            'requires_follow_up' => ['sometimes', 'boolean'],
            'requires_note' => ['sometimes', 'boolean'],
            'marks_lead_closed' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
