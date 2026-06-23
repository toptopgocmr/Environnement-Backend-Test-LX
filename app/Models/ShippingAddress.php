<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
