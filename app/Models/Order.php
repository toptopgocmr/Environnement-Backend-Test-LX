<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────────────────────────────────────
class Order extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'book_id', 'amount', 'currency', 'type',
        'payment_method', 'payment_status', 'transaction_id', 'payment_data',
        'download_token', 'download_count', 'max_downloads', 'expires_at',
        'shipping_address', 'shipping_city', 'shipping_phone', 'shipping_status',
        'full_name', 'shipping_country',
        'tracking_number', 'carrier', 'shipping_note',
        'estimated_delivery_date', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'payment_data'            => 'array',
        'expires_at'              => 'datetime',
        'shipped_at'              => 'datetime',
        'delivered_at'            => 'datetime',
        'estimated_delivery_date' => 'date',
        'amount'                  => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($o) {
            $o->reference = 'LRX-' . strtoupper(Str::random(8));
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }
    public function royalty() { return $this->hasOne(Royalty::class); }
    public function trackingEvents() { return $this->hasMany(OrderTrackingEvent::class)->orderBy('occurred_at', 'asc'); }
    public function shippingAddress() { return $this; } // compat — fields are on the order itself

    public function shippingStatusLabel(): string
    {
        return OrderTrackingEvent::statusLabel($this->shipping_status);
    }

    public function shippingStatusIcon(): string
    {
        return OrderTrackingEvent::statusIcon($this->shipping_status);
    }

    public function isPaid(): bool { return $this->payment_status === 'paid'; }
    public function canDownload(): bool
    {
        return $this->isPaid()
            && $this->download_count < $this->max_downloads
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
