<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;
    protected $fillable = ['name', 'email', 'password', 'approved_at', 'status', 'citizen_number', 'phone', 'social_handle', 'avatar_path', 'cover_path', 'profile_visibility', 'hide_contact'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'approved_at' => 'datetime', 'password' => 'hashed']; }
    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class); }
    public function posts(): HasMany { return $this->hasMany(Post::class); }
    public function verificationDocuments(): HasMany { return $this->hasMany(VerificationDocument::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
    public function hasRole(string $role): bool { return $this->roles()->where('name', $role)->exists(); }
    public function hasAnyRole(array $roles): bool { return $this->roles()->whereIn('name', $roles)->exists(); }
    public function canPost(): bool { return $this->status === 'active' && $this->approved_at !== null; }
    public function isBenefactor(): bool { return $this->hasRole('benefactor'); }
}