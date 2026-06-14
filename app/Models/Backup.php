<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    use BelongsToTenant;

    public const TYPE_MANUAL = 'manual';

    public const TYPE_AUTO = 'auto';

    public const TYPE_SCHEDULED = 'scheduled';

    public const TYPE_UPLOADED = 'uploaded';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'filename',
        'disk',
        'size_bytes',
        'type',
        'progress',
        'current_table',
        'status',
        'error_message',
        'tables_count',
        'total_rows',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'progress' => 'integer',
            'tables_count' => 'integer',
            'total_rows' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function markProcessing(): void
    {
        DB::table('backups')->where('id', $this->id)->update([
            'status' => self::STATUS_PROCESSING,
            'progress' => 0,
            'error_message' => null,
        ]);

        $this->status = self::STATUS_PROCESSING;
        $this->progress = 0;
    }

    public function markProgress(int $progress, string $currentTable = null): void
    {
        $clamped = min(max($progress, 0), 100);

        DB::table('backups')->where('id', $this->id)->update([
            'progress' => $clamped,
            'current_table' => $currentTable,
        ]);

        $this->progress = $clamped;
        $this->current_table = $currentTable;
    }

    public function markCompleted(int $sizeBytes, int $tablesCount, int $totalRows): void
    {
        DB::table('backups')->where('id', $this->id)->update([
            'status' => self::STATUS_COMPLETED,
            'filename' => $this->filename,
            'size_bytes' => $sizeBytes,
            'tables_count' => $tablesCount,
            'total_rows' => $totalRows,
            'progress' => 100,
            'completed_at' => Carbon::now(),
            'error_message' => null,
        ]);

        $this->status = self::STATUS_COMPLETED;
        $this->progress = 100;
        $this->size_bytes = $sizeBytes;
        $this->tables_count = $tablesCount;
        $this->total_rows = $totalRows;
        $this->completed_at = Carbon::now();
    }

    public function markFailed(string $message): void
    {
        DB::table('backups')->where('id', $this->id)->update([
            'status' => self::STATUS_FAILED,
            'error_message' => str($message)->limit(2000)->toString(),
        ]);

        $this->status = self::STATUS_FAILED;
    }

    public function getFilePath(): string
    {
        return "backups/tenant_{$this->tenant_id}/{$this->filename}";
    }

    public function fileExists(): bool
    {
        return $this->filename && Storage::disk($this->disk)->exists($this->getFilePath());
    }

    public function sizeForHumans(): string
    {
        if (! $this->size_bytes) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size_bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, $unitIndex > 0 ? 2 : 0) . ' ' . $units[$unitIndex];
    }

    public static function pruneOlderThan(int $days = 30): int
    {
        $cutoff = Carbon::now()->subDays($days);

        $oldBackups = static::query()
            ->withoutTenantScope()
            ->where('created_at', '<', $cutoff)
            ->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_FAILED])
            ->get();

        $count = 0;

        foreach ($oldBackups as $backup) {
            if ($backup->fileExists()) {
                Storage::disk($backup->disk)->delete($backup->getFilePath());
            }
            DB::table('backups')->where('id', $backup->id)->delete();
            $count++;
        }

        return $count;
    }
}
