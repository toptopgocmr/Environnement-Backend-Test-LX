<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─────────────────────────────────────────────────────────────────────────────
class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['conversation_id','sender_id','body','type','file_path','file_name','is_read','read_at'];

    protected $casts = ['is_read' => 'boolean', 'read_at' => 'datetime'];

    public function conversation() { return $this->belongsTo(ChatConversation::class, 'conversation_id'); }
    public function sender()       { return $this->belongsTo(User::class, 'sender_id'); }
}
