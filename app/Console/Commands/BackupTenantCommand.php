<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupTenantCommand extends Command
{
    protected $signature = 'backup:tenant
        {--tenant= : Backup a specific tenant by ID}
        {--all : Backup all active tenants}';

    protected $description = 'Create database backup for one or all tenants';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $all = $this->option('all');

        if (! $tenantId && ! $all) {
            $this->error('Gunakan --tenant=ID atau --all untuk backup semua tenant.');

            return self::FAILURE;
        }

        $user = \App\Models\User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Superadmin'))
            ->whereNull('tenant_id')
            ->first();

        if (! $user) {
            $this->error('Tidak ada superadmin ditemukan.');

            return self::FAILURE;
        }

        if ($tenantId) {
            $tenant = \App\Models\Tenant::find($tenantId);

            if (! $tenant) {
                $this->error("Tenant dengan ID {$tenantId} tidak ditemukan.");

                return self::FAILURE;
            }

            $this->backupTenant($tenant, $user);

            return self::SUCCESS;
        }

        $tenants = \App\Models\Tenant::query()
            ->whereIn('subscription_status', ['trial', 'active', 'grace'])
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant aktif ditemukan.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->backupTenant($tenant, $user);
        }

        $this->info("Backup selesai untuk {$tenants->count()} tenant.");

        return self::SUCCESS;
    }

    protected function backupTenant(\App\Models\Tenant $tenant, \App\Models\User $user): void
    {
        $this->line("Memproses: {$tenant->name}");

        $progressBar = $this->output->createProgressBar(100);
        $progressBar->setFormat("  %current%% [%bar%] %message%");
        $progressBar->setMessage('Menyiapkan...');
        $progressBar->start();

        try {
            $service = app(\App\Services\TenantBackupService::class);

            $backup = \App\Models\Backup::query()->create([
                'tenant_id' => $tenant->id,
                'created_by' => $user->id,
                'filename' => '',
                'disk' => config('backups.disk', 'local'),
                'type' => \App\Models\Backup::TYPE_SCHEDULED,
                'status' => \App\Models\Backup::STATUS_PROCESSING,
            ]);

            $result = $service->generateBackupSql($tenant, function (int $progress, ?string $table) use ($progressBar, $backup) {
                $progressBar->setProgress($progress);
                $progressBar->setMessage($table ?? 'Selesai');
                $backup->markProgress($progress, $table);
            });

            $content = $result['content'];
            $compressed = gzencode($content, 9);

            $date = now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$tenant->id}_{$date}.sql.gz";
            $directory = "backups/tenant_{$tenant->id}";

            \Illuminate\Support\Facades\Storage::disk(config('backups.disk', 'local'))
                ->makeDirectory($directory);
            \Illuminate\Support\Facades\Storage::disk(config('backups.disk', 'local'))
                ->put("{$directory}/{$filename}", $compressed);

            $backup->update([
                'filename' => $filename,
                'size_bytes' => strlen($compressed),
                'tables_count' => $result['tables_count'],
                'total_rows' => $result['total_rows'],
                'status' => \App\Models\Backup::STATUS_COMPLETED,
                'completed_at' => now(),
                'progress' => 100,
            ]);

            $progressBar->finish();
            $this->newLine(2);
            $this->info("  ✓ {$tenant->name}: {$result['total_rows']} baris dari {$result['tables_count']} tabel.");
        } catch (\Throwable $e) {
            $progressBar->finish();

            if (isset($backup)) {
                $backup->markFailed($e->getMessage());
            }

            $this->newLine(2);
            $this->error("  ✗ Gagal {$tenant->name}: {$e->getMessage()}");
        }
    }
}
