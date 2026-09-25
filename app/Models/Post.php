<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'sector', 'clearance', 'tags', 'status', 'is_pinned'];
    protected $casts = ['tags' => 'array', 'is_pinned' => 'boolean'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'id' => null,
            'name' => 'Unknown Operative',
            'designation' => 'Ginhawa Citizen',
            'citizen_number' => 'GHW-0000-0000-0000',
        ]);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

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
        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }
        return [];
    }
}