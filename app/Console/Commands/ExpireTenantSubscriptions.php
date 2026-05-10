<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantSubscriptionHistory;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireTenantSubscriptions extends Command
{
    protected $signature = 'saas:expire-subscriptions';

    protected $description = 'Expire overdue tenant trials, subscriptions, and grace periods.';

    public function handle(ActivityLogger $activityLogger): int
    {
        $now = now();
        $graceDays = max(0, (int) config('saas.grace_days', 0));
        $expiredTrials = 0;
        $graceStarted = 0;
        $expiredGracePeriods = 0;

        Tenant::query()
            ->where('subscription_status', Tenant::SUBSCRIPTION_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', $now)
            ->orderBy('id')
            ->each(function (Tenant $tenant) use ($activityLogger, &$expiredTrials): void {
                $this->expireTenant(
                    tenant: $tenant,
                    activityLogger: $activityLogger,
                    reason: 'trial_expired',
                    periodEndsAt: $tenant->trial_ends_at
                );

                $expiredTrials++;
            });

        Tenant::query()
            ->where('subscription_status', Tenant::SUBSCRIPTION_ACTIVE)
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<=', $now)
            ->orderBy('id')
            ->each(function (Tenant $tenant) use ($activityLogger, $graceDays, $now, &$expiredGracePeriods, &$graceStarted): void {
                if ($graceDays > 0) {
                    $this->startGracePeriod($tenant, $activityLogger, $now->copy()->addDays($graceDays));
                    $graceStarted++;

                    return;
                }

                $this->expireTenant(
                    tenant: $tenant,
                    activityLogger: $activityLogger,
                    reason: 'subscription_expired',
                    periodEndsAt: $tenant->subscription_ends_at
                );

                $expiredGracePeriods++;
            });

        Tenant::query()
            ->where('subscription_status', Tenant::SUBSCRIPTION_GRACE)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<=', $now)
            ->orderBy('id')
            ->each(function (Tenant $tenant) use ($activityLogger, &$expiredGracePeriods): void {
                $this->expireTenant(
                    tenant: $tenant,
                    activityLogger: $activityLogger,
                    reason: 'grace_period_expired',
                    periodEndsAt: $tenant->grace_ends_at
                );

                $expiredGracePeriods++;
            });

        $this->info("Tenant lifecycle sync completed. Trials expired: {$expiredTrials}. Grace started: {$graceStarted}. Grace/paid expired: {$expiredGracePeriods}.");

        return self::SUCCESS;
    }

    protected function expireTenant(
        Tenant $tenant,
        ActivityLogger $activityLogger,
        string $reason,
        mixed $periodEndsAt
    ): void {
        DB::transaction(function () use ($activityLogger, $periodEndsAt, $reason, $tenant): void {
            $previousStatus = $tenant->subscription_status;

            $tenant->forceFill([
                'subscription_status' => Tenant::SUBSCRIPTION_EXPIRED,
                'grace_ends_at' => $reason === 'grace_period_expired'
                    ? $tenant->grace_ends_at
                    : $tenant->grace_ends_at,
            ])->save();

            TenantSubscriptionHistory::query()->create([
                'tenant_id' => $tenant->id,
                'action' => 'mark_expired',
                'period_starts_at' => now(),
                'period_ends_at' => now(),
                'admin_note' => 'Status tenant otomatis diubah menjadi expired oleh scheduled task. Alasan: '.$reason.'.',
                'changed_by' => null,
            ]);

            $activityLogger->log(
                action: 'subscription_auto_expired',
                actor: null,
                target: $tenant,
                description: 'Status subscription tenant otomatis diubah menjadi expired.',
                properties: [
                    'actor_name' => 'System',
                    'reason' => $reason,
                    'before' => [
                        'subscription_status' => $previousStatus,
                    ],
                    'after' => [
                        'subscription_status' => Tenant::SUBSCRIPTION_EXPIRED,
                    ],
                    'period_ends_at' => $periodEndsAt?->toDateTimeString(),
                ]
            );
        });
    }

    protected function startGracePeriod(Tenant $tenant, ActivityLogger $activityLogger, mixed $graceEndsAt): void
    {
        DB::transaction(function () use ($activityLogger, $graceEndsAt, $tenant): void {
            $previousStatus = $tenant->subscription_status;

            $tenant->forceFill([
                'subscription_status' => Tenant::SUBSCRIPTION_GRACE,
                'grace_ends_at' => $graceEndsAt,
            ])->save();

            TenantSubscriptionHistory::query()->create([
                'tenant_id' => $tenant->id,
                'action' => 'mark_grace',
                'period_starts_at' => now(),
                'period_ends_at' => $graceEndsAt,
                'admin_note' => 'Tenant otomatis dipindahkan ke grace period oleh scheduled task karena subscription aktif telah berakhir.',
                'changed_by' => null,
            ]);

            $activityLogger->log(
                action: 'subscription_auto_grace_started',
                actor: null,
                target: $tenant,
                description: 'Tenant otomatis dipindahkan ke grace period.',
                properties: [
                    'actor_name' => 'System',
                    'reason' => 'subscription_expired',
                    'before' => [
                        'subscription_status' => $previousStatus,
                    ],
                    'after' => [
                        'subscription_status' => Tenant::SUBSCRIPTION_GRACE,
                        'grace_ends_at' => $graceEndsAt->toDateTimeString(),
                    ],
                ]
            );
        });
    }
}
