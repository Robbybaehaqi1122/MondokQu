<?php

namespace App\Console\Commands;

use App\Models\DataExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneDataExports extends Command
{
    protected $signature = 'exports:prune
        {--dry-run : Tampilkan jumlah export kedaluwarsa tanpa menghapus file atau database}';

    protected $description = 'Delete expired background export files and database records.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $pruned = 0;
        $failedDeletes = 0;

        DataExport::query()
            ->expired()
            ->where('status', '!=', DataExport::STATUS_PROCESSING)
            ->orderBy('id')
            ->each(function (DataExport $export) use ($dryRun, &$failedDeletes, &$pruned): void {
                if ($dryRun) {
                    $pruned++;

                    return;
                }

                if (! $this->deleteExportFiles($export)) {
                    $failedDeletes++;

                    return;
                }

                $export->delete();
                $pruned++;
            });

        if ($dryRun) {
            $this->info("{$pruned} expired data export(s) would be pruned.");

            return self::SUCCESS;
        }

        if ($failedDeletes > 0) {
            $this->warn("Data export pruning completed with {$failedDeletes} file delete failure(s). Pruned: {$pruned}.");

            return self::FAILURE;
        }

        $this->info("Data export pruning completed. Pruned: {$pruned}.");

        return self::SUCCESS;
    }

    protected function deleteExportFiles(DataExport $export): bool
    {
        if (! $export->path) {
            return true;
        }

        $disk = Storage::disk($export->disk);
        $directory = $this->exportDirectory($export);

        if ($directory) {
            if (! $disk->exists($export->path)) {
                return true;
            }

            return $disk->deleteDirectory($directory);
        }

        if (! $disk->exists($export->path)) {
            return true;
        }

        return $disk->delete($export->path);
    }

    protected function exportDirectory(DataExport $export): ?string
    {
        $expectedPrefix = 'exports/'.$export->id.'/';

        if (! str_starts_with((string) $export->path, $expectedPrefix)) {
            return null;
        }

        return rtrim($expectedPrefix, '/');
    }
}
