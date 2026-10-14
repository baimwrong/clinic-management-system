<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'import_type',
        'filename',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'error_log',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
            'error_log' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'bg-success',
            'completed_with_errors' => 'bg-warning text-dark',
            'failed' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
