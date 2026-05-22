<?php

namespace App\Livewire;

use App\Events\MessageSendEvent;
use App\Models\Message;
use App\Models\User;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatComponent extends Component
{
    public $user;
    public $sender_id;
    public $receiver_id;
    public $message = '';
    public $messages = [];

    public function render()
    {
        $products = Product::query()->orderBy('name')->take(20)->get();
        return view('livewire.chat-component', [
            'products' => $products,
        ]);
    }

    public function mount($user_id)
    {
        $this->sender_id = auth()->user()->id;
        $this->receiver_id = $user_id;

        $messages = Message::query()
            ->where(function ($query) {
                $query->where('sender_id', $this->sender_id)
                    ->where('receiver_id', $this->receiver_id);
            })->orWhere(function ($query) {
                $query->where('sender_id', $this->receiver_id)
                    ->where('receiver_id', $this->sender_id);
            })
            ->with(['sender:id,name', 'receiver:id,name'])
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse();

        foreach ($messages as $message) {
            $this->appendChatMessage($message);
        }

        $this->user = User::query()->select(['id', 'name', 'email'])->whereKey($user_id)->first();
    }

    public function sendMessage()
    {
        $chatMessage = new Message();
        $chatMessage->sender_id = $this->sender_id;
        $chatMessage->receiver_id = $this->receiver_id;
        $chatMessage->message = $this->message;
        $chatMessage->save();

        $chatMessage->load(['sender:id,name', 'receiver:id,name']);

        $this->appendChatMessage($chatMessage);
        broadcast(new MessageSendEvent($chatMessage))->toOthers();

        // ✅ Best way to clear input
        $this->reset('message');
    }

    public function sendProduct($productId)
    {
        $product = Product::find($productId);

        if ($product) {

            $chatMessage = new Message();
            $chatMessage->sender_id = $this->sender_id;
            $chatMessage->receiver_id = $this->receiver_id;
            $chatMessage->type = "product";
            $chatMessage->data = [
                "name" => $product->name,
                "price" => $product->sale_price,
                "image" => json_decode($product->images)[0],   // use your field name
            ];
            $chatMessage->message = $product->name; // no plain text needed
            $chatMessage->save();

            $chatMessage->load(['sender:id,name', 'receiver:id,name']);

            $this->appendChatMessage($chatMessage);

            broadcast(new MessageSendEvent($chatMessage))->toOthers();
        }
    }

    #[On('echo-private:chat-channel.{sender_id},MessageSendEvent')]
    public function listenForMessage($event)
    {
        $chatMessage = Message::whereId($event['message']['id'])
            ->with('sender:id,name', 'receiver:id,name')
            ->first();

        $this->appendChatMessage($chatMessage);
    }

    public function appendChatMessage($message)
    {
        $this->messages[] = [
            'id' => $message->id,
            'message' => $message->message,
            'type' => $message->type,
            'data' => $message->data,

            'sender' => $message->sender->name,
            'receiver' => $message->receiver->name,
            'time' => $message->created_at->format('h:i A'),
            'date' => $this->formatMessageDate($message->created_at),
            'sender_image' => $message->sender->profile_photo_url ?? asset('default-avatar.png'),
        ];
    }

    protected function formatMessageDate($timestamp)
    {
        if ($timestamp->isToday()) {
            return 'Today';
        } elseif ($timestamp->isYesterday()) {
            return 'Yesterday';
        } else {
            return $timestamp->format('d M Y'); // Example: 29 Oct 2025
        }
    }
}