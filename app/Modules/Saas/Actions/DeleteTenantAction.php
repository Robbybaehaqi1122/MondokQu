<?php

namespace App\Modules\Saas\Actions;

use App\Models\ActivityLog;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\Tenant;
use App\Models\TenantBillingNote;
use App\Models\TenantSubscriptionHistory;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SantriPhotoUploader;
use App\Services\UserAvatarUploader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DeleteTenantAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected SantriPhotoUploader $santriPhotoUploader,
        protected UserAvatarUploader $userAvatarUploader
    ) {}

    /**
     * Permanently delete a tenant and its tenant-owned operational data.
     *
     * @return array{deleted: bool, message: string, snapshot: array<string, mixed>|null}
     */
    public function handle(
        Tenant $tenant,
        ?User $actor,
        ?string $deleteReason = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        if ($actor && $this->tenantContainsUser($tenant, $actor)) {
            return [
                'deleted' => false,
                'message' => 'Tenant ini memuat akun yang sedang Anda gunakan, sehingga tidak bisa dihapus permanen.',
                'snapshot' => null,
            ];
        }

        $snapshot = $this->snapshot($tenant, $deleteReason);
        $santriPhotoPaths = $tenant->santris()
            ->withoutTenantScope()
            ->whereNotNull('photo_path')
            ->pluck('photo_path')
            ->all();
        $userAvatarPaths = $tenant->users()
            ->whereNotNull('avatar_path')
            ->pluck('avatar_path')
            ->all();
        $userIds = $tenant->users()->pluck('id')->all();
        $userEmails = $tenant->users()
            ->whereNotNull('email')
            ->pluck('email')
            ->all();

        $this->deleteTableRowsByValues('sessions', 'user_id', $userIds);
        $this->deleteTableRowsByValues('password_reset_tokens', 'email', $userEmails);

        DB::transaction(function () use ($tenant): void {
            $tenant->forceFill(['owner_id' => null])->save();
        });

        $this->deleteEloquentRowsInChunks(
            ActivityLog::query()->withoutTenantScope()->where('tenant_id', $tenant->id)
        );
        $this->deleteEloquentRowsInChunks(
            TenantBillingNote::query()->where('tenant_id', $tenant->id)
        );
        $this->deleteEloquentRowsInChunks(
            TenantSubscriptionHistory::query()->where('tenant_id', $tenant->id)
        );
        $this->deleteEloquentRowsInChunks(
            SantriPayment::query()->withoutTenantScope()->where('tenant_id', $tenant->id)
        );
        $this->deleteEloquentRowsInChunks(
            SantriInvoice::query()->withoutTenantScope()->where('tenant_id', $tenant->id)
        );
        $this->deleteEloquentRowsInChunks(
            Santri::query()->withoutTenantScope()->where('tenant_id', $tenant->id)
        );

        if ($userIds !== []) {
            User::query()
                ->whereIn('id', $userIds)
                ->chunkById(500, function ($users): void {
                    $users->each->delete();
                });
        }

        DB::transaction(function () use ($tenant): void {
            $tenant->delete();
        });

        foreach ($santriPhotoPaths as $path) {
            $this->santriPhotoUploader->deleteIfManaged($path);
        }

        foreach ($userAvatarPaths as $path) {
            $this->userAvatarUploader->deleteIfManaged($path);
        }

        $this->activityLogger->log(
            action: 'tenant_deleted_permanently',
            actor: $actor,
            target: $tenant,
            description: 'Tenant dihapus permanen dari panel SaaS.',
            properties: $snapshot,
            ipAddress: $ipAddress,
            userAgent: $userAgent
        );

        return [
            'deleted' => true,
            'message' => 'Tenant '.$snapshot['tenant_name'].' berhasil dihapus permanen.',
            'snapshot' => $snapshot,
        ];
    }

    protected function tenantContainsUser(Tenant $tenant, User $user): bool
    {
        return $tenant->users()
            ->whereKey($user->id)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    protected function snapshot(Tenant $tenant, ?string $deleteReason): array
    {
        return [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'tenant_slug' => $tenant->slug,
            'subscription_status' => $tenant->subscription_status,
            'users_count' => $tenant->users()->count(),
            'santris_count' => $tenant->santris()->withoutTenantScope()->count(),
            'santri_invoices_count' => $tenant->santriInvoices()->withoutTenantScope()->count(),
            'santri_payments_count' => $tenant->santriPayments()->withoutTenantScope()->count(),
            'activity_logs_count' => $tenant->activityLogs()->withoutTenantScope()->count(),
            'billing_notes_count' => $tenant->billingNotes()->count(),
            'subscription_histories_count' => $tenant->subscriptionHistories()->count(),
            'delete_reason' => $deleteReason,
        ];
    }

    /**
     * Delete table rows in bounded batches for large tenant cleanup jobs.
     *
     * @param  array<int, mixed>  $values
     */
    protected function deleteTableRowsByValues(string $table, string $column, array $values, int $chunkSize = 500): void
    {
        $values = array_values(array_filter($values, fn (mixed $value): bool => $value !== null && $value !== ''));

        foreach (array_chunk($values, $chunkSize) as $chunk) {
            DB::table($table)
                ->whereIn($column, $chunk)
                ->delete();
        }
    }

    /**
     * Delete model rows in bounded transactions so queue workers avoid giant locks.
     *
     * @param  Builder<Model>  $query
     */
    protected function deleteEloquentRowsInChunks(Builder $query, int $chunkSize = 500): void
    {
        do {
            $ids = (clone $query)
                ->orderBy($query->getModel()->getQualifiedKeyName())
                ->limit($chunkSize)
                ->pluck($query->getModel()->getKeyName());

            if ($ids->isEmpty()) {
                return;
            }

            DB::transaction(function () use ($ids, $query): void {
                (clone $query)
                    ->whereKey($ids->all())
                    ->delete();
            });
        } while ($ids->count() === $chunkSize);
    }
}
