<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

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
}
