<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ─────────────────────────────────────────────────────────────────────────────
class BookTag extends Model
{
    public $timestamps = false;
    protected $fillable = ['book_id', 'tag'];

    public function book() { return $this->belongsTo(Book::class); }
}
