<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function sharedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('permission')
            ->withTimestamps();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'author_id');
    }
}
