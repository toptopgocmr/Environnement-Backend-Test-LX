<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─────────────────────────────────────────────────────────────────────────────
class ChatConversation extends Model
{
    protected $fillable = ['type','book_id','order_id','subject','status','last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function messages()     { return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('created_at'); }
    public function lastMessage()  { return $this->hasOne(ChatMessage::class, 'conversation_id')->latest(); }
    public function participants() { return $this->hasMany(ChatParticipant::class, 'conversation_id'); }
    public function users()        { return $this->belongsToMany(User::class, 'chat_participants', 'conversation_id', 'user_id'); }
    public function book()         { return $this->belongsTo(Book::class); }
    public function order()        { return $this->belongsTo(Order::class); }

    public function unreadCountFor(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant || !$participant->last_read_at) {
            return $this->messages()->where('sender_id', '!=', $userId)->count();
        }
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }
}
