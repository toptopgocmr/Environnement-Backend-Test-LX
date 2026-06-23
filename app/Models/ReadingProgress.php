<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ─────────────────────────────────────────────────────────────────────────────
class ReadingProgress extends Model
{
    protected $fillable = ['user_id', 'book_id', 'current_page', 'total_pages', 'percentage', 'last_read_at'];
    protected $casts    = ['percentage' => 'decimal:2', 'last_read_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }
}
