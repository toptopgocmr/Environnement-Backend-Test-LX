<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTrackingEvent extends Model
{
    protected $fillable = [
        'order_id', 'status', 'location', 'description', 'occurred_at', 'created_by',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function statusLabel(string $status): string
    {
        return [
            'none'              => 'Commande reçue',
            'processing'        => 'En préparation',
            'shipped'           => 'Expédiée',
            'out_for_delivery'  => 'En cours de livraison',
            'delivered'         => 'Livrée',
            'failed'            => 'Échec de livraison',
            'cancelled'         => 'Annulée',
        ][$status] ?? ucfirst($status);
    }

    public static function statusIcon(string $status): string
    {
        return [
            'none'              => '📋',
            'processing'        => '📦',
            'shipped'           => '🚚',
            'out_for_delivery'  => '🏍️',
            'delivered'         => '✅',
            'failed'            => '❌',
            'cancelled'         => '🚫',
        ][$status] ?? '📍';
    }
}
