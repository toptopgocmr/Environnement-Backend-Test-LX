<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
