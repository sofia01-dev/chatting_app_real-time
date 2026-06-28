<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->message->id,
            'sender_id'    => $this->message->sender_id,
            'receiver_id'  => $this->message->receiver_id,
            'message'      => $this->message->message,
            'message_type' => $this->message->message_type,
            'file_path'    => $this->message->file_path
                                ? asset('storage/' . $this->message->file_path)
                                : null,
            'file_name'    => $this->message->file_name,
            'file_size'    => $this->message->file_size,
            'created_at'   => $this->message->created_at,
        ];
    }
}
