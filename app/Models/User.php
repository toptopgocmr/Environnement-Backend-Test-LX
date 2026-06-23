<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'avatar',
        'bio', 'domain', 'website', 'country', 'city',
        'mtn_number', 'airtel_number',
        'is_active', 'is_verified_author',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
        'is_verified_author'=> 'boolean',
    ];

    // JWT
    public function getJWTIdentifier(): mixed       { return $this->getKey(); }
    public function getJWTCustomClaims(): array     { return ['role' => $this->role]; }

    // Roles
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isAuthor(): bool  { return $this->role === 'author'; }
    public function isReader(): bool  { return $this->role === 'reader'; }

    // Relations
    public function books()           { return $this->hasMany(Book::class, 'author_id'); }
    public function orders()          { return $this->hasMany(Order::class); }
    public function royalties()       { return $this->hasMany(Royalty::class, 'author_id'); }
    public function reviews()         { return $this->hasMany(Review::class); }
    public function wishlists()       { return $this->belongsToMany(Book::class, 'wishlists'); }
    public function readingProgress() { return $this->hasMany(ReadingProgress::class); }
    public function withdrawals()     { return $this->hasMany(WithdrawalRequest::class, 'author_id'); }
    public function followers()       { return $this->belongsToMany(User::class, 'author_follows', 'author_id', 'follower_id'); }
    public function following()       { return $this->belongsToMany(User::class, 'author_follows', 'follower_id', 'author_id'); }
    public function subscriptions()   { return $this->hasMany(Subscription::class); }

    // Accessors
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=2563EB&background=EFF6FF';
    }

    public function getPendingBalanceAttribute(): float
    {
        return (float) $this->royalties()->where('status', 'pending')->sum('net_amount');
    }

    public function getTotalEarningsAttribute(): float
    {
        return (float) $this->royalties()->where('status', 'paid')->sum('net_amount');
    }

    public function getTotalSalesAttribute(): int
    {
        return $this->books()->withCount(['orders' => fn($q) => $q->where('payment_status', 'paid')])->get()->sum('orders_count');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()->where('status', 'active')->where('ends_at', '>', now())->exists();
    }

    public function hasPurchased(int $bookId): bool
    {
        return $this->orders()->where('book_id', $bookId)->where('payment_status', 'paid')->exists();
    }
}
