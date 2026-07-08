<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Support\Carbon;

class DataImport extends Model
{
    use BelongsToTenant;
    public const TYPE_SANTRI = 'santri';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'name',
        'status',
        'original_filename',
        'disk',
        'path',
        'summary',
        'filters',
        'total_rows',
        'success_rows',
        'failed_rows',
        'failure_message',
        'completed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'filters' => 'array',
            'total_rows' => 'integer',
            'success_rows' => 'integer',
            'failed_rows' => 'integer',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('user_id', $user->id);
    }

    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user && (int) $this->user_id === (int) $user->id;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function markProcessing(): void
    {
        $this->forceFill([
            'status' => self::STATUS_PROCESSING,
            'failure_message' => null,
        ])->save();
    }

    public function markCompleted(int $successRows, int $failedRows, array $summary): void
    {
        $this->forceFill([
            'status' => self::STATUS_COMPLETED,
            'success_rows' => $successRows,
            'failed_rows' => $failedRows,
            'summary' => $summary,
            'failure_message' => null,
            'completed_at' => Carbon::now(),
        ])->save();
    }

    public function markFailed(string $message): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'failure_message' => str($message)->limit(1000)->toString(),
        ])->save();
    }
}
