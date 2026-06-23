<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─────────────────────────────────────────────────────────────────────────────
class AuthorPlan extends Model
{
    protected $fillable = [
        'user_id','plan_id','billing','status','amount_paid',
        'currency','payment_method','transaction_id','starts_at','ends_at',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function plan() { return $this->belongsTo(PublicationPlan::class, 'plan_id'); }

    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
