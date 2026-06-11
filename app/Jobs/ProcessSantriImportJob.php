<?php

namespace App\Jobs;

use App\Imports\SantriImport;
use App\Models\DataImport;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessSantriImportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $dataImportId
    ) {}

    public function handle(ActivityLogger $activityLogger): void
    {
        $import = DataImport::query()->findOrFail($this->dataImportId);
        $user = $import->user_id ? User::query()->find($import->user_id) : null;

        if (! $user) {
            $import->markFailed('User pembuat import tidak ditemukan.');

            return;
        }

        $import->markProcessing();

        try {
            if (! $import->path || ! Storage::disk($import->disk ?? 'local')->exists($import->path)) {
                throw new \RuntimeException('Data file import tidak ditemukan.');
            }

            $data = json_decode(Storage::disk($import->disk ?? 'local')->get($import->path), true);
            $rows = collect($data['rows']);

            $importer = new SantriImport((int) $user->tenant_id, (int) $user->id);
            $result = $importer->import($rows);

            $import->markCompleted(
                successRows: $result['success_rows'],
                failedRows: $result['failed_rows'],
                summary: [
                    'errors' => $result['errors']->toArray(),
                    'total' => $result['total'],
                ]
            );

            $activityLogger->log(
                action: 'santri_imported',
                actor: $user,
                target: null,
                description: "Import data santri (background): {$result['success_rows']} berhasil, {$result['failed_rows']} gagal dari {$result['total']} baris.",
                properties: [
                    'success_rows' => $result['success_rows'],
                    'failed_rows' => $result['failed_rows'],
                    'total_rows' => $result['total'],
                    'data_import_id' => $import->id,
                ]
            );

            if ($import->path) {
                Storage::disk($import->disk ?? 'local')->delete($import->path);
            }
        } catch (Throwable $exception) {
            $import->markFailed($exception->getMessage());

            throw $exception;
        }
    }
}
