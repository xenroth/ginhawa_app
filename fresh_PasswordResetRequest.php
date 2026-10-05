<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    protected $fillable = ['email', 'user_id', 'status', 'note', 'handled_at'];
    protected $casts = ['handled_at' => 'datetime'];
}
