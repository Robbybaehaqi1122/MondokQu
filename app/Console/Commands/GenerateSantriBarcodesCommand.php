<?php

namespace App\Console\Commands;

use App\Models\Santri;
use Illuminate\Console\Command;

class GenerateSantriBarcodesCommand extends Command
{
    protected $signature = 'santri:generate-barcodes {tenant? : Specific tenant ID}';

    protected $description = 'Generate unique barcodes for santri that do not have one';

    public function handle(): int
    {
        $query = Santri::query()->whereNull('barcode');

        if ($tenantId = $this->argument('tenant')) {
            $query->where('tenant_id', $tenantId);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('Semua santri sudah memiliki barcode.');

            return self::SUCCESS;
        }

        $this->info("Mengenerate barcode untuk {$count} santri...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->each(function (Santri $santri) use ($bar): void {
            $santri->barcode = Santri::generateUniqueBarcode();
            $santri->saveQuietly();
            $bar->advance();
        }, 100);

        $bar->finish();
        $this->newLine();
        $this->info('Selesai!');

        return self::SUCCESS;
    }
}
