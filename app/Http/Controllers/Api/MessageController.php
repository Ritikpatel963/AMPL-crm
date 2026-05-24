<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSendEvent;
use App\Http\Controllers\Controller;
use App\Models\AgentCustomerAssignment;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function getMessages($user_id)
    {
        try {
            $authUser = auth()->user();
            $authId = $authUser->id;

            if (!$this->canMessageUser($authUser, (int) $user_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized conversation.',
                ], 403);
            }

            $messages = Message::where(function ($query) use ($authId, $user_id) {
                    $query->where('sender_id', $authId)
                          ->where('receiver_id', $user_id);
                })
                ->orWhere(function ($query) use ($authId, $user_id) {
                    $query->where('sender_id', $user_id)
                          ->where('receiver_id', $authId);
                })
                ->with('sender:id,name', 'receiver:id,name')
                ->orderBy('id', 'ASC')
                ->get();

            Message::where('sender_id', $user_id)
                ->where('receiver_id', $authId)
                ->whereNull('seen_at')
                ->update(['seen_at' => now()]);

            return response()->json([
                'status' => true,
                'messages' => $messages
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Get Messages Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to load messages'
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        if (!$this->canMessageUser($request->user(), (int) $request->receiver_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized conversation.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $msg = Message::create([
                'sender_id'   => auth()->id(),
                'receiver_id' => $request->receiver_id,
                'message'     => $request->message,
                'type'        => 'text',
            ]);

            broadcast(new MessageSendEvent($msg))->toOthers();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $msg
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Send Message Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Message could not be sent'
            ], 500);
        }
    }

    public function sendProduct(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'product_id'  => 'required|exists:products,id',
        ]);

        if (!$this->canMessageUser($request->user(), (int) $request->receiver_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized conversation.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            $images = json_decode($product->images, true);

            $msg = Message::create([
                'sender_id'   => auth()->id(),
                'receiver_id' => $request->receiver_id,
                'type'        => 'product',
                'message'     => $product->name,
                'data'        => [
                    'name'  => $product->name,
                    'price' => $product->sale_price,
                    'image' => $images[0] ?? null,
                ],
            ]);

            broadcast(new MessageSendEvent($msg))->toOthers();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => $msg
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Send Product Message Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Product message could not be sent'
            ], 500);
        }
    }

    public function markAsSeen($user_id)
    {
        try {
            if (!$this->canMessageUser(auth()->user(), (int) $user_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized conversation.',
                ], 403);
            }

            DB::beginTransaction();

            Message::where('sender_id', $user_id)
                ->where('receiver_id', auth()->id())
                ->whereNull('seen_at')
                ->update(['seen_at' => now()]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Messages marked as seen'
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Mark As Seen Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to mark messages as seen'
            ], 500);
        }
    }

    public function getLatestMessage($user_id)
    {
        try {
            $authUser = auth()->user();

            if (!$this->canMessageUser($authUser, (int) $user_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized conversation.',
                ], 403);
            }

            $message = Message::where(function ($query) use ($authUser, $user_id) {
                    $query->where('sender_id', $authUser->id)
                        ->where('receiver_id', $user_id);
                })
                ->orWhere(function ($query) use ($authUser, $user_id) {
                    $query->where('sender_id', $user_id)
                        ->where('receiver_id', $authUser->id);
                })
                ->latest()
                ->first();

            return response()->json([
                'status' => true,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('Latest Message Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to load latest message',
            ], 500);
        }
    }

    private function canMessageUser(User $authUser, int $otherUserId): bool
    {
        if ($authUser->id === $otherUserId) {
            return false;
        }

        if (!User::whereKey($otherUserId)->exists()) {
            return false;
        }

        if ($authUser->role === 'subadmin') {
            return true;
        }

        if ($authUser->role === 'agent') {
            return AgentCustomerAssignment::where('agent_id', $authUser->id)
                ->where('customer_id', $otherUserId)
                ->exists();
        }

        if ($authUser->role === 'customer') {
            return AgentCustomerAssignment::where('customer_id', $authUser->id)
                ->where('agent_id', $otherUserId)
                ->exists();
        }

        return false;
    }
}
