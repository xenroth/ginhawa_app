<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReaction extends Model
{
    protected $table = 'comment_reactions';
    protected $fillable = ['comment_id', 'user_id', 'value'];

    public function comment(): BelongsTo { return $this->belongsTo(Comment::class); }
}
