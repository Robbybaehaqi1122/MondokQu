<?php

namespace App\Modules\Saas\Actions;

use App\Models\Tenant;
use App\Models\TenantSubscriptionHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateTenantSubscriptionAction
{
    /**
     * Update the tenant subscription state and persist a history record.
     *
     * @param  array<string, mixed>  $validated
     * @return array{message: string, history: TenantSubscriptionHistory}
     */
    public function handle(Tenant $tenant, array $validated, ?User $actor = null): array
    {
        $action = (string) $validated['action'];
        $actionedAt = now();
        $trialEndsAt = ! empty($validated['trial_ends_at']) ? Carbon::parse((string) $validated['trial_ends_at']) : null;
        $graceEndsAt = ! empty($validated['grace_ends_at']) ? Carbon::parse((string) $validated['grace_ends_at']) : null;
        $subscriptionEndAt = match ($validated['subscription_duration'] ?? null) {
            '1_month' => $actionedAt->copy()->addMonth(),
            '3_months' => $actionedAt->copy()->addMonths(3),
            '6_months' => $actionedAt->copy()->addMonths(6),
            '12_months' => $actionedAt->copy()->addYear(),
            default => null,
        };

        /** @var array{message: string, history: TenantSubscriptionHistory} $result */
        $result = DB::transaction(function () use ($action, $actionedAt, $actor, $graceEndsAt, $subscriptionEndAt, $tenant, $trialEndsAt, $validated): array {
            match ($action) {
                'activate_trial', 'extend_trial' => $tenant->forceFill([
                    'subscription_plan' => config('saas.default_plan', 'trial'),
                    'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
                    'trial_ends_at' => $trialEndsAt,
                    'subscription_starts_at' => null,
                    'subscription_ends_at' => null,
                    'grace_ends_at' => null,
                ])->save(),
                'activate_subscription' => $tenant->forceFill([
                    'subscription_plan' => 'basic',
                    'subscription_status' => Tenant::SUBSCRIPTION_ACTIVE,
                    'subscription_starts_at' => $actionedAt,
                    'subscription_ends_at' => $subscriptionEndAt,
                    'grace_ends_at' => null,
                ])->save(),
                'mark_grace' => $tenant->forceFill([
                    'subscription_plan' => $tenant->subscription_plan ?: 'basic',
                    'subscription_status' => Tenant::SUBSCRIPTION_GRACE,
                    'subscription_ends_at' => $tenant->subscription_ends_at ?: $actionedAt,
                    'grace_ends_at' => $graceEndsAt,
                ])->save(),
                'mark_expired' => $tenant->forceFill([
                    'subscription_plan' => $tenant->subscription_plan ?: 'basic',
                    'subscription_status' => Tenant::SUBSCRIPTION_EXPIRED,
                    'grace_ends_at' => $actionedAt->copy()->subSecond(),
                ])->save(),
            };

            [$periodStartsAt, $periodEndsAt] = match ($action) {
                'activate_trial', 'extend_trial' => [$actionedAt, $trialEndsAt],
                'activate_subscription' => [$actionedAt, $subscriptionEndAt],
                'mark_grace' => [$actionedAt, $graceEndsAt],
                'mark_expired' => [$actionedAt, $actionedAt],
                default => [null, null],
            };

            $history = TenantSubscriptionHistory::query()->create([
                'tenant_id' => $tenant->id,
                'action' => $action,
                'period_starts_at' => $periodStartsAt,
                'period_ends_at' => $periodEndsAt,
                'admin_note' => filled($validated['admin_note'] ?? null) ? (string) $validated['admin_note'] : null,
                'changed_by' => $actor?->id,
            ]);

            $message = match ($action) {
                'activate_trial' => 'Trial tenant berhasil diaktifkan dengan tanggal akhir yang dipilih.',
                'extend_trial' => 'Masa trial tenant berhasil diatur ulang sesuai tanggal yang dipilih.',
                'activate_subscription' => 'Subscription tenant berhasil diaktifkan sesuai durasi yang dipilih.',
                'mark_grace' => 'Tenant berhasil dipindahkan ke masa grace period sesuai tanggal yang dipilih.',
                'mark_expired' => 'Tenant berhasil ditandai sebagai expired.',
                default => 'Subscription tenant berhasil diperbarui.',
            };

            return [
                'message' => $message,
                'history' => $history,
            ];
        });

        return $result;
    }
}
