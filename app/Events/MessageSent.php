<?php
namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\{Channel, InteractsWithSockets, PrivateChannel};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly ChatMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'sender'          => [
                'id'     => $this->message->sender->id,
                'name'   => $this->message->sender->name,
                'avatar' => $this->message->sender->avatar_url,
            ],
            'body'       => $this->message->body,
            'type'       => $this->message->type,
            'file_name'  => $this->message->file_name,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
