<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'icon',
        'status',
        'is_public',
        'share_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class)->orderBy('position');
    }

    public function rootFolders(): HasMany
    {
        return $this->hasMany(Folder::class)->whereNull('parent_id')->orderBy('position');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class)->orderBy('title');
    }

    public function rootDocuments(): HasMany
    {
        return $this->hasMany(Document::class)->whereNull('folder_id')->orderBy('title');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('permission')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    /**
     * Scope projects visible to the given user (owner, member, or admin).
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user): void {
            $q->where('owner_id', $user->id)
                ->orWhereHas('members', function (Builder $m) use ($user): void {
                    $m->where('users.id', $user->id);
                });
        });
    }

    /**
     * Determine the effective role/permission of a user on this project.
     */
    public function roleFor(User $user): ?string
    {
        if ($user->isAdmin() || $this->owner_id === $user->id) {
            return 'owner';
        }

        if ($this->relationLoaded('members')) {
            $member = $this->members->firstWhere('id', $user->id);

            return $member?->pivot?->permission;
        }

        $member = $this->members()->where('users.id', $user->id)->first();

        return $member?->pivot?->permission;
    }

    /**
     * Check if the user can view this project and its documents.
     */
    public function canView(User $user): bool
    {
        return $this->roleFor($user) !== null;
    }

    /**
     * Check if the user can create, edit, move, publish, or delete documents and folders.
     */
    public function canEdit(User $user): bool
    {
        return in_array($this->roleFor($user), ['owner', 'manager', 'editor'], true);
    }

    /**
     * Check if the user can manage project settings, members, sharing, or delete the project.
     */
    public function canManage(User $user): bool
    {
        return in_array($this->roleFor($user), ['owner', 'manager'], true);
    }

    /**
     * Generate a unique share token for public access.
     */
    public static function generateShareToken(): string
    {
        do {
            $token = Str::random(24);
        } while (self::where('share_token', $token)->exists());

        return $token;
    }

    /**
     * Determine whether the project is publicly accessible.
     */
    public function isPubliclyAccessible(): bool
    {
        return $this->is_public && ! empty($this->share_token);
    }

    /**
     * Get the public share URL if enabled.
     */
    public function publicShareUrl(): ?string
    {
        if (! $this->isPubliclyAccessible()) {
            return null;
        }

        return route('public.projects.show', ['token' => $this->share_token]);
    }
}
