<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'actor_id', 'type', 'subject_type', 'subject_id', 'body', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }

    public function user() { return $this->belongsTo(User::class, 'user_id'); }

    public function subject() { return $this->morphTo(); }

    /** Fire a notification. Silently skips self-actions and failures. */
    public static function fire(int $userId, ?int $actorId, string $type, $subject = null, ?string $body = null): void
    {
        if ($actorId !== null && $actorId === $userId) {
            return; // never notify people about their own actions
        }
        try {
            static::create([
                'user_id' => $userId,
                'actor_id' => $actorId,
                'type' => $type,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'body' => $body,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification failed.', ['error' => $e->getMessage(), 'user_id' => $userId, 'type' => $type]);
        }
    }

    public function icon(): string
    {
        return match ($this->type) {
            'upvote' => 'fa-arrow-up',
            'comment' => 'fa-comment',
            'reply' => 'fa-reply',
            'react_comment' => 'fa-face-smile',
            'tip' => 'fa-coins',
            'connect' => 'fa-user-plus',
            'message' => 'fa-envelope',
            default => 'fa-bell',
        };
    }

    public function label(): string
    {
        $who = $this->actor?->name ?? 'A member';
        return match ($this->type) {
            'upvote' => "$who upvoted your transmission",
            'comment' => "$who commented on your transmission",
            'reply' => "$who replied to your comment",
            'react_comment' => "$who reacted to your comment",
            'tip' => "$who sent you a tip",
            'connect' => "$who wants to connect",
            'message' => "$who sent you a message",
            default => "$who pinged you",
        };
    }
}
