<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'zone', 'label', 'base_price', 'price_per_kg',
        'free_above', 'estimated_days_min', 'estimated_days_max',
        'is_active', 'notes',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /** Calcule les frais pour un montant de commande et un poids en grammes */
    public function calculateFee(int $orderAmount = 0, int $weightGrams = 0): int
    {
        if ($this->free_above > 0 && $orderAmount >= $this->free_above) {
            return 0;
        }
        $fee = $this->base_price;
        if ($weightGrams > 1000 && $this->price_per_kg > 0) {
            $extraKg = ceil(($weightGrams - 1000) / 1000);
            $fee += $extraKg * $this->price_per_kg;
        }
        return $fee;
    }

    public function getDeliveryRangeAttribute(): string
    {
        if ($this->estimated_days_min === $this->estimated_days_max) {
            return "{$this->estimated_days_min} jour(s)";
        }
        return "{$this->estimated_days_min}–{$this->estimated_days_max} jours";
    }

    public static function forZone(string $zone): ?self
    {
        return static::where('zone', $zone)->where('is_active', true)->first();
    }

    public static function detectZone(string $countryCode): string
    {
        return strtoupper($countryCode) === 'CG' ? 'congo' : 'international';
    }
}
