<?php

namespace App\Models;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QbSyncLog extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'tenant_id',
        'direction',
        'status',
        'started_at',
        'completed_at',
        'families_processed',
        'payments_processed',
        'conflicts_found',
        'errors',
        'triggered_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'direction'    => SyncDirection::class,
            'status'       => SyncStatus::class,
            'started_at'   => 'datetime',
            'completed_at' => 'datetime',
            'errors'       => 'array',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function markRunning(): void
    {
        $this->update(['status' => SyncStatus::Running, 'started_at' => now()]);
    }

    public function markCompleted(int $families = 0, int $payments = 0, int $conflicts = 0): void
    {
        $this->update([
            'status'             => SyncStatus::Completed,
            'completed_at'       => now(),
            'families_processed' => $families,
            'payments_processed' => $payments,
            'conflicts_found'    => $conflicts,
        ]);
    }

    public function markFailed(array $errors): void
    {
        $this->update([
            'status'       => SyncStatus::Failed,
            'completed_at' => now(),
            'errors'       => $errors,
        ]);
    }
}
