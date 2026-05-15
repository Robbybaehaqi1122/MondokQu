<?php

namespace App\Modules\Saas\Jobs;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Saas\Actions\DeleteTenantAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteTenantJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 900;

    public function __construct(
        public int $tenantId,
        public ?int $actorId,
        public ?string $deleteReason = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null
    ) {}

    /**
     * Permanently delete a tenant that has been marked as deleting.
     */
    public function handle(DeleteTenantAction $deleteTenant): void
    {
        $tenant = Tenant::query()->find($this->tenantId);

        if (! $tenant || ! $tenant->isDeleting()) {
            return;
        }

        $deleteTenant->handle(
            tenant: $tenant,
            actor: $this->actorId ? User::query()->find($this->actorId) : null,
            deleteReason: $this->deleteReason,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent
        );
    }
}
