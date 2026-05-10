<?php

namespace App\Modules\Saas\Actions;

use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SantriPhotoUploader;
use App\Services\UserAvatarUploader;
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
        User $actor,
        ?string $deleteReason = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        if ($this->tenantContainsUser($tenant, $actor)) {
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

        DB::transaction(function () use ($tenant, $userEmails, $userIds): void {
            DB::table('sessions')
                ->when($userIds !== [], fn ($query) => $query->whereIn('user_id', $userIds))
                ->when($userIds === [], fn ($query) => $query->whereRaw('1 = 0'))
                ->delete();

            DB::table('password_reset_tokens')
                ->when($userEmails !== [], fn ($query) => $query->whereIn('email', $userEmails))
                ->when($userEmails === [], fn ($query) => $query->whereRaw('1 = 0'))
                ->delete();

            $tenant->forceFill(['owner_id' => null])->save();

            $tenant->activityLogs()->withoutTenantScope()->delete();
            $tenant->billingNotes()->delete();
            $tenant->subscriptionHistories()->delete();
            $tenant->santriPayments()->withoutTenantScope()->delete();
            $tenant->santriInvoices()->withoutTenantScope()->delete();
            $tenant->santris()->withoutTenantScope()->delete();

            User::query()
                ->whereIn('id', $userIds)
                ->get()
                ->each
                ->delete();

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
}
