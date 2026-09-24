<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'sector', 'clearance', 'tags', 'status', 'is_pinned'];
    protected $casts = ['tags' => 'array', 'is_pinned' => 'boolean'];
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function scopeVisible($query, ?User $user = null) { return $query->where('status', 'approved')->when(!$user || !$user->hasAnyRole(['administrator', 'moderator']), fn ($q) => $q->where('clearance', 'public')); }
}