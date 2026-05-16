<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentCustomerAssignment;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;

class AgentCustomerController extends Controller
{
    public function getAssignedCustomers(Request $request)
    {
        $agent = $request->user();

        if ($agent->role !== 'agent') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $customers = User::where('role', 'customer')
            ->whereIn('id', function ($q) use ($agent) {
                $q->select('customer_id')
                    ->from('agent_customer_assignments')
                    ->where('agent_id', $agent->id);
            })
            ->select('id', 'name', 'email')
            ->with([
                'sentMessages' => function ($q) use ($agent) {
                    $q->where('receiver_id', $agent->id)
                        ->latest()
                        ->limit(1);
                },
                'receivedMessages' => function ($q) use ($agent) {
                    $q->where('sender_id', $agent->id)
                        ->latest()
                        ->limit(1);
                }
            ])
            ->get()
            ->map(function ($customer) use ($agent) {

                $latest = collect([
                    $customer->sentMessages->first(),
                    $customer->receivedMessages->first()
                ])->filter()->sortByDesc('created_at')->first();

                $unread = Message::where('sender_id', $customer->id)
                    ->where('receiver_id', $agent->id)
                    ->whereNull('seen_at')
                    ->count();

                $latestMessageText = 'Tap to chat';
                if ($latest) {
                    if ($latest->sender_id === $agent->id) {
                        $latestMessageText = 'You: ' . $latest->message;
                    } else {
                        $latestMessageText = $latest->message;
                    }
                }

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'latest_message' => $latestMessageText,
                    'latest_message_time' => $latest?->created_at,
                    'unread_count' => $unread
                ];
            })
            ->sortByDesc('latest_message_time')
            ->values();

        return response()->json([
            'status' => true,
            'customers' => $customers
        ]);
    }

    public function getCustomerAgent(Request $request)
    {
        $user = $request->user();  // logged in user

        if ($user->role !== 'customer') {
            return response()->json([
                'status' => false,
                'message' => 'Only customers can request agent ID'
            ], 403);
        }

        $relation = AgentCustomerAssignment::where('customer_id', $user->id)
            ->orderBy('id', 'DESC')   // latest assigned agent
            ->first();

        if (!$relation) {
            return response()->json([
                'status' => false,
                'message' => 'Agent not assigned yet',
                'agent_id' => null
            ]);
        }

        return response()->json([
            'status' => true,
            'agent_id' => $relation->agent_id
        ]);
    }
}
