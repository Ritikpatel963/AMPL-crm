<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentCustomerAssignment;
use App\Models\CustomerCallLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerCallController extends Controller
{
    public function start(Request $request)
    {
        $customer = $request->user();

        abort_if($customer->role !== 'customer', 403);

        $data = $request->validate([
            'agent_id' => ['required', 'exists:users,id'],
            'phone_number' => ['required', 'string', 'max:30'],
            'direction' => ['sometimes', Rule::in(['outgoing', 'incoming'])],
        ]);

        $assigned = AgentCustomerAssignment::where('customer_id', $customer->id)
            ->where('agent_id', $data['agent_id'])
            ->exists();

        abort_if(! $assigned, 403);

        $call = CustomerCallLog::create([
            'customer_id' => $customer->id,
            'agent_id' => $data['agent_id'],
            'phone_number' => $data['phone_number'],
            'direction' => $data['direction'] ?? 'outgoing',
            'status' => 'initiated',
            'started_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Call started successfully',
            'data' => $call,
        ], 201);
    }

    public function update(Request $request, CustomerCallLog $call)
    {
        $this->authorizeCall($request, $call);

        $data = $request->validate([
            'status' => ['required', Rule::in(['initiated', 'connected', 'not_connected', 'failed'])],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $call->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Call updated successfully',
            'data' => $call->fresh(),
        ]);
    }

    public function uploadRecording(Request $request, CustomerCallLog $call)
    {
        $this->authorizeCall($request, $call);

        $request->validate([
            'recording' => ['required', 'file', 'max:51200'],
        ]);

        $path = $request->file('recording')
            ->store("customer-calls/recordings/{$call->id}", 'public');

        $call->update([
            'recording_url' => Storage::disk('public')->url($path),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Recording uploaded successfully',
            'data' => $call->fresh(),
        ]);
    }

    private function authorizeCall(Request $request, CustomerCallLog $call): void
    {
        $user = $request->user();

        abort_if(! $user, 403);
        abort_if($user->role === 'customer' && (int) $call->customer_id !== (int) $user->id, 403);
        abort_if($user->role === 'agent' && (int) $call->agent_id !== (int) $user->id, 403);
    }
}
