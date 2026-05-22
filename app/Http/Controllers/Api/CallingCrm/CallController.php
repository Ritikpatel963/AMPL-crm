<?php

namespace App\Http\Controllers\Api\CallingCrm;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CallController extends Controller
{
    public function index(Request $request)
    {
        $calls = CallLog::with(['lead', 'campaign', 'user', 'disposition'])
            ->when($request->filled('lead_id'), fn ($query) => $query->where('lead_id', $request->lead_id))
            ->when($request->filled('campaign_id'), fn ($query) => $query->where('campaign_id', $request->campaign_id))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('started_at', '<=', $request->to))
            ->latest('started_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $calls]);
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'phone_number' => ['required', 'string', 'max:20'],
            'direction' => ['sometimes', Rule::in(['outgoing', 'incoming'])],
            'provider_call_id' => ['nullable', 'string', 'max:160'],
        ]);

        $lead = Lead::findOrFail($data['lead_id']);

        $call = CallLog::create([
            'lead_id' => $lead->id,
            'campaign_id' => $lead->campaign_id,
            'user_id' => auth()->id() ?? $lead->assigned_user_id,
            'direction' => $data['direction'] ?? 'outgoing',
            'status' => 'initiated',
            'provider_call_id' => $data['provider_call_id'] ?? null,
            'phone_number' => $data['phone_number'],
            'started_at' => now(),
            'called_at' => now(),
        ]);

        $lead->update(['last_call_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Call started successfully',
            'data' => $call,
        ], 201);
    }

    public function update(Request $request, CallLog $call)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['initiated', 'ringing', 'connected', 'answered', 'not_connected', 'busy', 'no_answer', 'failed', 'missed'])],
            'answered_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'ring_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'recording_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        $call->update($data);
        $call->lead?->update(['last_call_at' => $call->ended_at ?? now()]);

        return response()->json([
            'status' => true,
            'message' => 'Call updated successfully',
            'data' => $call->fresh(['lead', 'campaign', 'user']),
        ]);
    }

    public function show(CallLog $call)
    {
        return response()->json([
            'status' => true,
            'data' => $call->load(['lead', 'campaign', 'user', 'disposition', 'leadDisposition']),
        ]);
    }

    public function webhook(Request $request)
    {
        $data = $request->validate([
            'provider_call_id' => ['required', 'string', 'max:160'],
            'status' => ['required', Rule::in(['initiated', 'ringing', 'connected', 'answered', 'not_connected', 'busy', 'no_answer', 'failed', 'missed'])],
            'started_at' => ['nullable', 'date'],
            'answered_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'ring_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'recording_url' => ['nullable', 'string', 'max:500'],
        ]);

        $call = CallLog::where('provider_call_id', $data['provider_call_id'])->first();

        if (!$call) {
            Log::warning('Call webhook received for unknown provider_call_id', ['provider_call_id' => $data['provider_call_id']]);
            return response()->json(['status' => false, 'message' => 'Call not found'], 404);
        }

        $call->update($data);

        if ($call->lead && in_array($data['status'], ['connected', 'answered'])) {
            $call->lead->update(['last_call_at' => $data['ended_at'] ?? now()]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Call webhook processed successfully',
            'data' => $call->fresh(),
        ]);
    }

    public function campaignCallLogs(Request $request, Campaign $campaign)
    {
        $calls = CallLog::where('campaign_id', $campaign->id)
            ->with(['lead:id,name,phone', 'user:id,name', 'disposition:id,name'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('started_at', '<=', $request->to))
            ->latest('started_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $calls]);
    }

    public function userCallLogs(Request $request, User $user)
    {
        $calls = CallLog::where('user_id', $user->id)
            ->with(['lead:id,name,phone', 'campaign:id,name', 'disposition:id,name'])
            ->when($request->filled('campaign_id'), fn ($q) => $q->where('campaign_id', $request->campaign_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('started_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('started_at', '<=', $request->to))
            ->latest('started_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['status' => true, 'data' => $calls]);
    }
}
