<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'action',
        'description',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a team activity log entry.
     */
    public static function record(?int $projectId, ?int $userId, string $action, string $description): self
    {
        return self::create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
