<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'sector', 'clearance', 'tags', 'status', 'is_pinned', 'media'];
    protected $casts = ['tags' => 'array', 'is_pinned' => 'boolean', 'media' => 'array'];
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'user_id')->withDefault(['name' => 'Unknown operative']); }
    public function comments() { return $this->hasMany(Comment::class); }
    public function reactions(): HasMany { return $this->hasMany(Reaction::class); }
    public function scopeVisible($query, ?User $user = null)
    {
        $query->where('status', 'approved');
        if (!$user || !$user->hasAnyRole(['administrator', 'moderator'])) {
            $query->whereIn('clearance', ['public', 'member']);
        }
        return $query;
    }

    public function getTagsAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        return $value ? (json_decode($value, true) ?: []) : [];
    }
}
