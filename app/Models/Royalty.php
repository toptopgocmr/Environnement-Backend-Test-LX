<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ─────────────────────────────────────────────────────────────────────────────
class Royalty extends Model
{
    protected $fillable = [
        'author_id', 'order_id', 'gross_amount',
        'platform_fee', 'net_amount', 'currency', 'status', 'paid_at',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount'   => 'decimal:2',
        'paid_at'      => 'datetime',
    ];

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function order()  { return $this->belongsTo(Order::class); }
}
