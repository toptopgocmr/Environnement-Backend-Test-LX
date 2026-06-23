<?php
// app/Models/V2Models.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ─────────────────────────────────────────────────────────────────────────────
class PublicationPlan extends Model
{
    protected $fillable = [
        'name','slug','description','price_monthly','price_annual','currency',
        'max_books','max_file_size_mb','allow_physical','allow_audio',
        'allow_academic','royalty_percent','ai_review','is_active','sort_order','features',
    ];

    protected $casts = [
        'features'        => 'array',
        'allow_physical'  => 'boolean',
        'allow_audio'     => 'boolean',
        'allow_academic'  => 'boolean',
        'ai_review'       => 'boolean',
        'is_active'       => 'boolean',
        'price_monthly'   => 'decimal:2',
        'price_annual'    => 'decimal:2',
        'royalty_percent' => 'decimal:2',
    ];

    public function authorPlans() { return $this->hasMany(AuthorPlan::class, 'plan_id'); }

    public function getPriceMonthlyFormattedAttribute(): string
    {
        return number_format($this->price_monthly, 0, ',', ' ') . ' ' . $this->currency . '/mois';
    }
}

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

// ─────────────────────────────────────────────────────────────────────────────
class AccountRequest extends Model
{
    protected $fillable = [
        'user_id','type','motivation','document_path',
        'institution_name','institution_country',
        'status','admin_note','reviewed_by','reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function user()       { return $this->belongsTo(User::class); }
    public function reviewer()   { return $this->belongsTo(User::class, 'reviewed_by'); }
}

// ─────────────────────────────────────────────────────────────────────────────
class AiReview extends Model
{
    protected $fillable = [
        'book_id','status','score_overall','score_originality','score_structure',
        'score_language','score_norms','summary','issues','suggestions',
        'isbn_valid','detected_language','detected_document_type',
        'plagiarism_flag','plagiarism_score','recommendation',
        'admin_decision_note','analyzed_at',
    ];

    protected $casts = [
        'issues'           => 'array',
        'suggestions'      => 'array',
        'isbn_valid'       => 'boolean',
        'plagiarism_flag'  => 'boolean',
        'plagiarism_score' => 'decimal:2',
        'analyzed_at'      => 'datetime',
    ];

    public function book() { return $this->belongsTo(Book::class); }

    public function getScoreBadgeAttribute(): string
    {
        $score = $this->score_overall ?? 0;
        if ($score >= 80) return 'excellent';
        if ($score >= 60) return 'bon';
        if ($score >= 40) return 'moyen';
        return 'faible';
    }

    public function getRecommendationColorAttribute(): string
    {
        return match($this->recommendation) {
            'approve' => 'green',
            'review'  => 'yellow',
            'reject'  => 'red',
            default   => 'gray',
        };
    }
}

// ─────────────────────────────────────────────────────────────────────────────
class ReadingSession extends Model
{
    protected $fillable = [
        'user_id','book_id','token','status','amount_paid','currency',
        'duration_hours','starts_at','expires_at','pages_read',
    ];

    protected $casts = [
        'starts_at'   => 'datetime',
        'expires_at'  => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function book() { return $this->belongsTo(Book::class); }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
class ShippingAddress extends Model
{
    protected $fillable = [
        'user_id','full_name','phone','address_line1','address_line2',
        'city','state','postal_code','country','is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}

// ─────────────────────────────────────────────────────────────────────────────
class PhysicalStockMovement extends Model
{
    protected $fillable = ['book_id','type','quantity','stock_after','reason','created_by'];

    public function book()    { return $this->belongsTo(Book::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}

// ─────────────────────────────────────────────────────────────────────────────
class ChatConversation extends Model
{
    protected $fillable = ['type','book_id','order_id','subject','status','last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function messages()     { return $this->hasMany(ChatMessage::class, 'conversation_id')->orderBy('created_at'); }
    public function lastMessage()  { return $this->hasOne(ChatMessage::class, 'conversation_id')->latest(); }
    public function participants() { return $this->hasMany(ChatParticipant::class, 'conversation_id'); }
    public function users()        { return $this->belongsToMany(User::class, 'chat_participants', 'conversation_id', 'user_id'); }
    public function book()         { return $this->belongsTo(Book::class); }
    public function order()        { return $this->belongsTo(Order::class); }

    public function unreadCountFor(int $userId): int
    {
        $participant = $this->participants()->where('user_id', $userId)->first();
        if (!$participant || !$participant->last_read_at) {
            return $this->messages()->where('sender_id', '!=', $userId)->count();
        }
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
class ChatParticipant extends Model
{
    protected $fillable = ['conversation_id','user_id','last_read_at','is_muted'];

    protected $casts = ['last_read_at' => 'datetime', 'is_muted' => 'boolean'];

    public function conversation() { return $this->belongsTo(ChatConversation::class, 'conversation_id'); }
    public function user()         { return $this->belongsTo(User::class); }
}

// ─────────────────────────────────────────────────────────────────────────────
class ChatMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['conversation_id','sender_id','body','type','file_path','file_name','is_read','read_at'];

    protected $casts = ['is_read' => 'boolean', 'read_at' => 'datetime'];

    public function conversation() { return $this->belongsTo(ChatConversation::class, 'conversation_id'); }
    public function sender()       { return $this->belongsTo(User::class, 'sender_id'); }
}

// ─────────────────────────────────────────────────────────────────────────────
class Citation extends Model
{
    protected $fillable = ['book_id','user_id','style','citation_text'];

    public function book() { return $this->belongsTo(Book::class); }
    public function user() { return $this->belongsTo(User::class); }
}
