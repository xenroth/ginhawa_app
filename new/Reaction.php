<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $table = 'post_reactions';
    protected $fillable = ['post_id', 'user_id', 'value'];

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
}
