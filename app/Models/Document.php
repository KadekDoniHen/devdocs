<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'folder_id',
        'author_id',
        'last_editor_id',
        'title',
        'slug',
        'summary',
        'content',
        'content_markdown',
        'status',
        'version',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'version' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($document) {
            if (empty($document->slug)) {
                $document->slug = Str::slug($document->title);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_editor_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class)->orderByDesc('revision_number');
    }

    public function apiEndpoints(): HasMany
    {
        return $this->hasMany(ApiEndpoint::class)->orderBy('name');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'document_tags');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DocumentComment::class)->with('user')->oldest();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
