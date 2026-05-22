<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $followUps = FollowUp::query()
            ->with([
                'lead:id,name,phone,email',
                'campaign:id,name',
                'user:id,name',
                'createdBy:id,name',
            ])
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('scheduled_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('scheduled_at', '<=', $request->to))
            ->orderBy('scheduled_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $followUps]);
    }

    public function store(Request $request)
    {
        $data = $this->validateFollowUp($request);
        $data['created_by'] = $data['created_by'] ?? auth()->id();
        $data['status'] = $data['status'] ?? 'scheduled';

        $followUp = FollowUp::create($data);
        $followUp->lead?->update(['next_follow_up_at' => $followUp->scheduled_at]);

        return response()->json([
            'status' => true,
            'message' => 'Follow-up scheduled successfully',
            'data' => $followUp->load([
                'lead:id,name,phone,email',
                'campaign:id,name',
                'user:id,name',
            ]),
        ], 201);
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $followUp->update($this->validateFollowUp($request, true));
        $followUp->lead?->update(['next_follow_up_at' => $followUp->scheduled_at]);

        return response()->json([
            'status' => true,
            'message' => 'Follow-up updated successfully',
            'data' => $followUp->fresh([
                'lead:id,name,phone,email',
                'campaign:id,name',
                'user:id,name',
            ]),
        ]);
    }

    public function complete(FollowUp $followUp)
    {
        $followUp->update([
            'status' => 'completed',
            'completed_at' => now(),
            'is_missed' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Follow-up completed successfully',
            'data' => $followUp->fresh(),
        ]);
    }

    public function destroy(FollowUp $followUp)
    {
        $followUp->update(['status' => 'cancelled']);
        $followUp->delete();

        return response()->json(['status' => true, 'message' => 'Follow-up cancelled successfully']);
    }

    private function validateFollowUp(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'lead_id' => [$required, 'exists:leads,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'call_log_id' => ['nullable', 'exists:call_logs,id'],
            'scheduled_at' => [$required, 'date'],
            'completed_at' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::in(['scheduled', 'due', 'completed', 'missed', 'cancelled', 'rescheduled'])],
            'note' => ['nullable', 'string'],
            'is_system_generated' => ['sometimes', 'boolean'],
        ]);
    }
}
