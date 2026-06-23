<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id', 'category_id', 'title', 'slug', 'description',
        'cover_image', 'file_path', 'preview_path', 'format', 'document_type',
        'price', 'currency', 'is_free', 'language', 'pages',
        'isbn', 'publication_year', 'publisher', 'status',
        'rejection_reason', 'is_featured', 'print_on_demand', 'print_price',
        'university', 'supervisor', 'field_of_study', 'keywords',
        'physical_stock', 'physical_price', 'allow_rental', 'rental_price_hour',
    ];

    protected $appends = ['cover_url'];

    protected $casts = [
        'price'          => 'decimal:2',
        'print_price'    => 'decimal:2',
        'average_rating' => 'decimal:2',
        'is_free'        => 'boolean',
        'is_featured'    => 'boolean',
        'print_on_demand'=> 'boolean',
    ];

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) return null;
        // Chemin public absolu (ex: /covers/xxx.svg ou /covers/xxx.jpg)
        if (str_starts_with($this->cover_image, '/')) {
            return config('app.url') . $this->cover_image;
        }
        // Storage Laravel
        return Storage::url($this->cover_image);
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Book $book) {
            if (empty($book->slug)) {
                $book->slug = Str::slug($book->title) . '-' . Str::random(5);
            }
        });
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePublished($q)  { return $q->where('status', 'published'); }
    public function scopeFeatured($q)   { return $q->where('is_featured', true); }
    public function scopeFree($q)       { return $q->where('is_free', true); }
    public function scopePending($q)    { return $q->where('status', 'pending'); }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function author()          { return $this->belongsTo(User::class, 'author_id'); }
    public function category()        { return $this->belongsTo(Category::class); }
    public function orders()          { return $this->hasMany(Order::class); }
    public function physicalOrders()  { return $this->hasMany(Order::class)->where('type', 'print'); }
    public function reviews()         { return $this->hasMany(Review::class); }
    public function tags()            { return $this->hasMany(BookTag::class); }
    public function wishlists()       { return $this->belongsToMany(User::class, 'wishlists'); }
    public function progress()        { return $this->hasMany(ReadingProgress::class); }
    public function readingSessions() { return $this->hasMany(ReadingSession::class); }
    public function stockMovements()  { return $this->hasMany(PhysicalStockMovement::class); }
    public function aiReview()        { return $this->hasOne(AiReview::class); }
}
