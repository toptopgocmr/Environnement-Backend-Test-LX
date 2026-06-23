<?php

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
