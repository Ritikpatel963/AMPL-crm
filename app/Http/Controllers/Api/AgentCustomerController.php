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
            ->where('approval_status', 'approved')
            ->whereIn('id', function ($q) use ($agent) {
                $q->select('customer_id')
                    ->from('agent_customer_assignments')
                    ->where('agent_id', $agent->id)
                    ->whereIn('id', AgentCustomerAssignment::query()
                        ->selectRaw('max(id)')
                        ->groupBy('customer_id'));
            })
            ->select('id', 'name', 'email', 'phone_number')
            ->get()
            ->map(function ($customer) use ($agent) {

                $latest = Message::where('sender_id', $customer->id)
                    ->orWhere('receiver_id', $customer->id)
                    ->latest()
                    ->first();

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
                    'phone_number' => $customer->phone_number,
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
            ->with('agent:id,name,email,phone_number')
            ->latest('id')
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
            'agent_id' => $relation->agent_id,
            'agent' => $relation->agent,
        ]);
    }
}
