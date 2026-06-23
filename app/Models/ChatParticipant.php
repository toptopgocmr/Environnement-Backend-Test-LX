<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─────────────────────────────────────────────────────────────────────────────
class ChatParticipant extends Model
{
    protected $fillable = ['conversation_id','user_id','last_read_at','is_muted'];

    protected $casts = ['last_read_at' => 'datetime', 'is_muted' => 'boolean'];

    public function conversation() { return $this->belongsTo(ChatConversation::class, 'conversation_id'); }
    public function user()         { return $this->belongsTo(User::class); }
}
