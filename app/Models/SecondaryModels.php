<?php
// Fichier regroupant les modèles secondaires de LireX
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

// ─────────────────────────────────────────────────────────────────────────────
class Review extends Model
{
    protected $fillable = ['book_id', 'user_id', 'rating', 'comment', 'is_approved'];
    protected $casts    = ['is_approved' => 'boolean', 'rating' => 'integer'];

    public function book() { return $this->belongsTo(Book::class); }
    public function user() { return $this->belongsTo(User::class); }
}

// ─────────────────────────────────────────────────────────────────────────────
class ReadingProgress extends Model
{
    protected $fillable = ['user_id', 'book_id', 'current_page', 'total_pages', 'percentage', 'last_read_at'];
    protected $casts    = ['percentage' => 'decimal:2', 'last_read_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }
}

// ─────────────────────────────────────────────────────────────────────────────
class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'plan', 'price', 'currency',
        'status', 'starts_at', 'ends_at', 'stripe_subscription_id',
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'price'     => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }
}

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

// ─────────────────────────────────────────────────────────────────────────────
class BookTag extends Model
{
    public $timestamps = false;
    protected $fillable = ['book_id', 'tag'];

    public function book() { return $this->belongsTo(Book::class); }
}
