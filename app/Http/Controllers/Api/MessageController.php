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
    public function getCurrentConversation(Request $request)
    {
        $receiverId = $this->resolveReceiverId($request);

        if (!$receiverId) {
            return response()->json([
                'status' => false,
                'message' => 'No assigned agent found for this customer.',
                'messages' => [],
            ], 422);
        }

        return $this->getMessages($receiverId);
    }

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

            $messages = $this->conversationQuery($authUser, (int) $user_id)
                ->with('sender:id,name', 'receiver:id,name')
                ->when(request()->filled('after_id'), fn ($query) => $query->where('id', '>', request()->integer('after_id')))
                ->orderBy('id', 'ASC')
                ->get();

            $this->markConversationAsSeen($authUser, (int) $user_id);

            return response()->json([
                'status' => true,
                'chat_user_id' => (int) $user_id,
                'messages' => $messages->map(fn (Message $message) => $this->messagePayload($message))->values(),
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
            'receiver_id' => 'nullable|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        $receiverId = $this->resolveReceiverId($request);

        if (!$receiverId) {
            return response()->json([
                'status' => false,
                'message' => 'No assigned agent found for this customer.',
            ], 422);
        }

        if (!$this->canMessageUser($request->user(), $receiverId)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized conversation.',
            ], 403);
        }

        try {
            $msg = Message::create([
                'sender_id'   => auth()->id(),
                'receiver_id' => $receiverId,
                'message'     => $request->message,
                'type'        => 'text',
            ]);

            $this->broadcastMessage($msg);

            return response()->json([
                'status' => true,
                'message' => $this->messagePayload($msg)
            ], 201);

        } catch (\Throwable $e) {
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

            DB::commit();

            $this->broadcastMessage($msg);

            return response()->json([
                'status' => true,
                'message' => $this->messagePayload($msg)
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

            $message = $this->conversationQuery($authUser, (int) $user_id)
                ->latest()
                ->first();

            return response()->json([
                'status' => true,
                'message' => $message ? $this->messagePayload($message) : null,
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
            return (int) AgentCustomerAssignment::where('customer_id', $otherUserId)
                ->latest('id')
                ->value('agent_id') === $authUser->id;
        }

        if ($authUser->role === 'customer') {
            return (int) AgentCustomerAssignment::where('customer_id', $authUser->id)
                ->latest('id')
                ->value('agent_id') === $otherUserId;
        }

        return false;
    }

    private function resolveReceiverId(Request $request): ?int
    {
        if ($request->filled('receiver_id')) {
            return (int) $request->receiver_id;
        }

        $user = $request->user();

        if ($user?->role !== 'customer') {
            return null;
        }

        return AgentCustomerAssignment::where('customer_id', $user->id)
            ->latest('id')
            ->value('agent_id');
    }

    private function conversationQuery(User $authUser, int $otherUserId)
    {
        return Message::where(function ($query) use ($authUser, $otherUserId) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($query) use ($authUser, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                    ->where('receiver_id', $authUser->id);
            });
    }

    private function markConversationAsSeen(User $authUser, int $otherUserId): void
    {
        Message::where('sender_id', $otherUserId)
            ->where('receiver_id', $authUser->id)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);
    }

    private function broadcastMessage(Message $message): void
    {
        try {
            broadcast(new MessageSendEvent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Message broadcast failed after save', [
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function messagePayload(Message $message): array
    {
        $message->loadMissing('sender:id,name', 'receiver:id,name');

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'type' => $message->type,
            'message' => $message->message,
            'data' => $message->data,
            'seen_at' => $message->seen_at,
            'sender' => $message->sender,
            'receiver' => $message->receiver,
            'created_at' => $message->created_at?->toDateTimeString(),
            'created_at_formatted' => $message->created_at_formatted,
        ];
    }
}
