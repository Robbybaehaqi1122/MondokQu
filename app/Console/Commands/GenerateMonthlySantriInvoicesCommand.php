<?php

namespace App\Console\Commands;

use App\Actions\Santri\GenerateMonthlySantriInvoices;
use App\Models\Tenant;
use Illuminate\Console\Command;

class GenerateMonthlySantriInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'santri:generate-monthly-invoices
        {--tenant= : Tenant ID tertentu. Kosongkan untuk semua tenant aktif}
        {--title=SPP Bulanan : Nama tagihan}
        {--month= : Bulan periode tagihan}
        {--year= : Tahun periode tagihan}
        {--due-date= : Tanggal jatuh tempo, format YYYY-MM-DD}
        {--amount= : Nominal tagihan}
        {--notes= : Catatan tagihan}
        {--dry-run : Hitung tanpa membuat tagihan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tagihan bulanan untuk santri aktif secara idempotent';

    /**
     * Execute the console command.
     */
    public function handle(GenerateMonthlySantriInvoices $generator): int
    {
        $month = (int) ($this->option('month') ?: now()->month);
        $year = (int) ($this->option('year') ?: now()->year);
        $dueDate = (string) ($this->option('due-date') ?: now()->endOfMonth()->toDateString());
        $amount = (int) ((float) $this->option('amount') * 100);
        $title = trim((string) $this->option('title'));

        if ($amount <= 0) {
            $this->error('Opsi --amount wajib diisi dan harus lebih dari 0.');

            return self::FAILURE;
        }

        if ($month < 1 || $month > 12) {
            $this->error('Opsi --month harus berada di antara 1 sampai 12.');

            return self::FAILURE;
        }

        $tenants = Tenant::query()
            ->when($this->option('tenant'), fn ($query, $tenantId) => $query->whereKey($tenantId))
            ->whereIn('subscription_status', [
                Tenant::SUBSCRIPTION_TRIAL,
                Tenant::SUBSCRIPTION_ACTIVE,
                Tenant::SUBSCRIPTION_GRACE,
            ])
            ->orderBy('id')
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant yang cocok untuk diproses.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $result = $generator->handle(
                tenantId: $tenant->id,
                title: $title,
                periodMonth: $month,
                periodYear: $year,
                dueDate: $dueDate,
                amount: $amount,
                notes: $this->option('notes'),
                dryRun: (bool) $this->option('dry-run')
            );

            $this->line(sprintf(
                '%s: %s dibuat, %s dilewati, %s santri aktif.',
                $tenant->name,
                number_format($result['created']),
                number_format($result['skipped']),
                number_format($result['eligible'])
            ));
        }

        return self::SUCCESS;
    }
}
