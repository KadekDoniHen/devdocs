<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'name',
        'method',
        'path',
        'description',
        'authentication',
        'headers',
        'parameters',
        'request_body',
        'responses',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'parameters' => 'array',
            'request_body' => 'array',
            'responses' => 'array',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
