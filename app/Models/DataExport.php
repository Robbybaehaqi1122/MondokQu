<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DataExport extends Model
{
    public const TYPE_SANTRI = 'santri';

    public const TYPE_SANTRI_INVOICES = 'santri_invoices';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'format',
        'name',
        'status',
        'disk',
        'path',
        'filename',
        'filters',
        'row_count',
        'failure_message',
        'completed_at',
        'expires_at',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'row_count' => 'integer',
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
        return $this->status === self::STATUS_COMPLETED && filled($this->path);
    }

    public function markProcessing(): void
    {
        $this->forceFill([
            'status' => self::STATUS_PROCESSING,
            'failure_message' => null,
        ])->save();
    }

    public function markCompleted(string $path, string $filename, int $rowCount): void
    {
        $this->forceFill([
            'status' => self::STATUS_COMPLETED,
            'path' => $path,
            'filename' => $filename,
            'row_count' => $rowCount,
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
