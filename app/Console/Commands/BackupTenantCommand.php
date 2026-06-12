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
        $this->info("Memulai backup untuk tenant: {$tenant->name}...");

        try {
            $service = app(\App\Services\TenantBackupService::class);
            $service->storeBackup($tenant, $user, \App\Models\Backup::TYPE_SCHEDULED);
            $this->info("  ✓ Backup {$tenant->name} selesai.");
        } catch (\Throwable $e) {
            $this->error("  ✗ Gagal backup {$tenant->name}: {$e->getMessage()}");
        }
    }
}
