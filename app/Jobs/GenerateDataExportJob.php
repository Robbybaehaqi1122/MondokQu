<?php

namespace App\Jobs;

use App\Exports\SantriCsvExport;
use App\Exports\SantriInvoiceCsvExport;
use App\Models\DataExport;
use App\Models\User;
use App\Notifications\DataExportCompletedNotification;
use App\Services\ActivityLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class GenerateDataExportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $dataExportId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ActivityLogger $activityLogger): void
    {
        $export = DataExport::query()->findOrFail($this->dataExportId);
        $user = $export->user_id ? User::query()->find($export->user_id) : null;

        if (! $user) {
            $export->markFailed('User pembuat export tidak ditemukan.');

            return;
        }

        $export->markProcessing();

        try {
            [$path, $filename, $rowCount] = match ($export->type) {
                DataExport::TYPE_SANTRI => app(SantriCsvExport::class)->store($export, $user, $export->filters ?? []),
                DataExport::TYPE_SANTRI_INVOICES => app(SantriInvoiceCsvExport::class)->store($export, $user, $export->filters ?? []),
                default => throw new RuntimeException('Jenis export tidak dikenal.'),
            };

            $export->markCompleted($path, $filename, $rowCount);

            $activityLogger->log(
                action: 'data_export_completed',
                actor: $user,
                target: $export,
                description: 'Export data selesai diproses.',
                properties: [
                    'type' => $export->type,
                    'filename' => $filename,
                    'row_count' => $rowCount,
                ]
            );

            $user->notify(new DataExportCompletedNotification($export));
        } catch (Throwable $exception) {
            $export->markFailed($exception->getMessage());

            throw $exception;
        }
    }
}
