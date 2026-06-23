<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ─────────────────────────────────────────────────────────────────────────────
class WithdrawalRequest extends Model
{
    protected $fillable = [
        'author_id', 'amount', 'currency', 'method',
        'account_number', 'account_name', 'status',
        'rejection_reason', 'balance_before', 'balance_after',
    ];
    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
    ];

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
}
