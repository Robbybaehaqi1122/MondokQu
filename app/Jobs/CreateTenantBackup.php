<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\TenantBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class CreateTenantBackup implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public Backup $backup
    ) {}

    public function handle(TenantBackupService $service): void
    {
        $service->storeBackup($this->backup);
    }
}
