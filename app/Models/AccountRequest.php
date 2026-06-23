<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
