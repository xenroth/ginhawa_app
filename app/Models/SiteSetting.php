<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'type'];
    public static function value(string $key, mixed $default = null): mixed { return static::where('key', $key)->value('value') ?? $default; }
}