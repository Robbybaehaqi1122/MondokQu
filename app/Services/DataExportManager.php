<?php

namespace App\Services;

use App\Jobs\GenerateDataExportJob;
use App\Models\DataExport;
use App\Models\User;

class DataExportManager
{
    public function shouldQueue(int $rowCount): bool
    {
        return $rowCount > $this->inlineThreshold();
    }

    public function inlineThreshold(): int
    {
        return max(0, (int) config('exports.inline_threshold', 5000));
    }

    public function queue(User $user, string $type, string $name, string $filename, array $filters, int $rowCount): DataExport
    {
        $export = DataExport::query()->create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'type' => $type,
            'name' => $name,
            'status' => DataExport::STATUS_QUEUED,
            'disk' => (string) config('exports.disk', 'local'),
            'filename' => $filename,
            'filters' => $filters,
            'row_count' => $rowCount,
            'expires_at' => now()->addDays(max(1, (int) config('exports.retention_days', 7))),
        ]);

        GenerateDataExportJob::dispatch($export->id);

        return $export;
    }
}
