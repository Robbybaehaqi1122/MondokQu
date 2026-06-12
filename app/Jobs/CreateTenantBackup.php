<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class CreateTenantBackup implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public string $type = 'manual'
    ) {}

    public function handle(TenantBackupService $service): void
    {
        $service->storeBackup($this->tenant, $this->user, $this->type);
    }
}
