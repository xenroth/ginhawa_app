<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Directive extends Model
{
    protected $fillable = ['title', 'body', 'pillar', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];
}