<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────────────────────────────────────
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'parent_id', 'is_active', 'sort_order'];

    public function books()    { return $this->hasMany(Book::class); }
    public function parent()   { return $this->belongsTo(Category::class, 'parent_id'); }
    public function children() { return $this->hasMany(Category::class, 'parent_id'); }
}
