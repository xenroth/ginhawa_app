<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationDocument extends Model
{
    protected $fillable = ['user_id', 'valid_id_path', 'social_handle', 'mobile_number', 'status', 'admin_notes'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}