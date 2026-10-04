<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'sort_order', 'requires_approval'];
    protected $casts = ['is_active' => 'boolean', 'requires_approval' => 'boolean'];
}