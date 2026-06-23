<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─────────────────────────────────────────────────────────────────────────────
class PhysicalStockMovement extends Model
{
    protected $fillable = ['book_id','type','quantity','stock_after','reason','created_by'];

    public function book()    { return $this->belongsTo(Book::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
