<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id', 'category_id', 'title', 'slug', 'description',
        'cover_image', 'file_path', 'preview_path', 'format',
        'price', 'currency', 'is_free', 'language', 'pages',
        'isbn', 'publication_year', 'publisher', 'status',
        'rejection_reason', 'is_featured', 'print_on_demand', 'print_price',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'print_price'    => 'decimal:2',
        'average_rating' => 'decimal:2',
        'is_free'        => 'boolean',
        'is_featured'    => 'boolean',
        'print_on_demand'=> 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Book $book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title) . '-' . Str::random(5);
            }
        });
    }

    // Scopes
    public function scopePublished($q)  { return $q->where('status', 'published'); }
    public function scopeFeatured($q)   { return $q->where('is_featured', true); }
    public function scopeFree($q)       { return $q->where('is_free', true); }
    public function scopePending($q)    { return $q->where('status', 'pending'); }

    // Relations
    public function author()     { return $this->belongsTo(User::class, 'author_id'); }
    public function category()   { return $this->belongsTo(Category::class); }
    public function orders()     { return $this->hasMany(Order::class); }
    public function reviews()    { return $this->hasMany(Review::class); }
    public function tags()       { return $this->hasMany(BookTag::class); }
    public function wishlists()  { return $this->belongsToMany(User::class, 'wishlists'); }
    public function progress()   { return $this->hasMany(ReadingProgress::class); }

    // Accessors
    public function getCoverUrlAttribute(): string
    {
        return $this->cover_image
            ? asset('storage/' . $this->cover_image)
            : asset('assets/img/default-cover.jpg');
    }

    public function getPriceFormattedAttribute(): string
    {
        if ($this->is_free) return 'Gratuit';
        return number_format($this->price, 0, ',', ' ') . ' ' . $this->currency;
    }

    public function getTotalRevenueAttribute(): float
    {
        return (float) $this->orders()->where('payment_status', 'paid')->sum('amount');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'parent_id', 'is_active', 'sort_order'];

    public function books()    { return $this->hasMany(Book::class); }
    public function parent()   { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children() { return $this->hasMany(Category::class, 'parent_id'); }
}

// ─────────────────────────────────────────────────────────────────────────────
class Order extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'book_id', 'amount', 'currency', 'type',
        'payment_method', 'payment_status', 'transaction_id', 'payment_data',
        'download_token', 'download_count', 'max_downloads', 'expires_at',
        'shipping_address', 'shipping_city', 'shipping_phone', 'shipping_status',
    ];

    protected $casts = [
        'payment_data' => 'array',
        'expires_at'   => 'datetime',
        'amount'       => 'decimal:2',
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

    public function isPaid(): bool { return $this->payment_status === 'paid'; }
    public function canDownload(): bool
    {
        return $this->isPaid()
            && $this->download_count < $this->max_downloads
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}

// ─────────────────────────────────────────────────────────────────────────────
class Royalty extends Model
{
    protected $fillable = ['author_id', 'order_id', 'gross_amount', 'platform_fee', 'net_amount', 'currency', 'status', 'paid_at'];
    protected $casts    = ['paid_at' => 'datetime', 'gross_amount' => 'decimal:2', 'platform_fee' => 'decimal:2', 'net_amount' => 'decimal:2'];

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function order()  { return $this->belongsTo(Order::class); }
}

// ─────────────────────────────────────────────────────────────────────────────
class Review extends Model
{
    protected $fillable = ['book_id', 'user_id', 'rating', 'comment', 'is_approved'];
    protected $casts    = ['is_approved' => 'boolean'];

    public function book() { return $this->belongsTo(Book::class); }
    public function user() { return $this->belongsTo(User::class); }
}

class BookTag extends Model
{
    public $timestamps = false;
    protected $fillable = ['book_id', 'tag'];
    public function book() { return $this->belongsTo(Book::class); }
}

class ReadingProgress extends Model
{
    protected $fillable = ['user_id', 'book_id', 'current_page', 'total_pages', 'percentage', 'last_read_at'];
    protected $casts    = ['last_read_at' => 'datetime', 'percentage' => 'decimal:2'];
    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }
}

class WithdrawalRequest extends Model
{
    protected $fillable = ['author_id', 'amount', 'currency', 'method', 'account_number', 'account_name', 'status', 'rejection_reason', 'balance_before', 'balance_after'];
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
}

class Subscription extends Model
{
    protected $fillable = ['user_id', 'plan', 'price', 'currency', 'status', 'starts_at', 'ends_at', 'stripe_subscription_id'];
    protected $casts    = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function isActive(): bool { return $this->status === 'active' && $this->ends_at->isFuture(); }
}
