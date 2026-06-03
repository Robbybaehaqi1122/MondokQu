<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminDashboardController extends \App\Http\Controllers\Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        $currentUser = request()->user();
        $tenant = $currentUser?->tenant;
        $cachedData = $this->dashboardCacheTtl() > 0
            ? Cache::remember(
                $this->dashboardCacheKey($currentUser),
                now()->addSeconds($this->dashboardCacheTtl()),
                fn (): array => $this->dashboardService->buildCachedDashboardData($currentUser)
            )
            : $this->dashboardService->buildCachedDashboardData($currentUser);

        return view('admin.dashboard', [
            'tenantSummary' => $this->buildTenantSummary($tenant, $currentUser?->isSuperAdmin() ?? false),
            'tenantLifecycleNotice' => $this->buildTenantLifecycleNotice($tenant, $currentUser?->isSuperAdmin() ?? false),
            ...$cachedData,
        ]);
    }

    protected function dashboardCacheKey(?User $currentUser): string
    {
        $scope = $currentUser?->isSuperAdmin()
            ? 'platform'
            : 'tenant:'.($currentUser?->tenant_id ?? 'none');

        return implode(':', [
            'admin-dashboard',
            $scope,
            now()->format('Y-m-d'),
            now()->format('o-W'),
            now()->format('Y-m'),
        ]);
    }

    protected function dashboardCacheTtl(): int
    {
        return max(0, (int) config('cache.dashboard_ttl_seconds', 300));
    }

    protected function buildTenantSummary(?Tenant $tenant, bool $isSuperAdmin): array
    {
        if ($isSuperAdmin) {
            return [
                'title' => 'Akun Platform',
                'badge' => 'Superadmin',
                'badge_color' => 'azure',
                'description' => 'Anda sedang melihat dashboard lintas tenant sebagai pengelola platform.',
                'meta' => 'Gunakan panel SaaS untuk memantau trial, subscription, dan billing semua tenant.',
            ];
        }

        if (! $tenant) {
            return [
                'title' => 'Tenant Belum Terhubung',
                'badge' => 'Perlu Tindak Lanjut',
                'badge_color' => 'warning',
                'description' => 'Akun ini belum terhubung ke tenant pondok.',
                'meta' => 'Hubungi Superadmin agar akun Anda ditempatkan pada tenant yang benar.',
            ];
        }

        $statusLabel = match ($tenant->subscription_status) {
            Tenant::SUBSCRIPTION_TRIAL => 'Masa Trial',
            Tenant::SUBSCRIPTION_ACTIVE => 'Subscription Aktif',
            Tenant::SUBSCRIPTION_GRACE => 'Grace Period',
            Tenant::SUBSCRIPTION_EXPIRED => 'Expired',
            default => 'Status Tidak Diketahui',
        };

        $badgeColor = match ($tenant->subscription_status) {
            Tenant::SUBSCRIPTION_TRIAL => 'azure',
            Tenant::SUBSCRIPTION_ACTIVE => 'success',
            Tenant::SUBSCRIPTION_GRACE => 'warning',
            Tenant::SUBSCRIPTION_EXPIRED => 'danger',
            default => 'secondary',
        };

        $meta = match ($tenant->subscription_status) {
            Tenant::SUBSCRIPTION_TRIAL => 'Trial berakhir pada '.optional($tenant->trial_ends_at)->translatedFormat('d M Y H:i'),
            Tenant::SUBSCRIPTION_ACTIVE => 'Akses aktif sampai '.optional($tenant->subscription_ends_at)->translatedFormat('d M Y H:i'),
            Tenant::SUBSCRIPTION_GRACE => 'Grace period berakhir pada '.optional($tenant->grace_ends_at)->translatedFormat('d M Y H:i'),
            Tenant::SUBSCRIPTION_EXPIRED => 'Akses tenant saat ini sudah berakhir dan perlu aktivasi ulang.',
            default => 'Periksa status subscription tenant Anda.',
        };

        return [
            'title' => $tenant->name,
            'badge' => $statusLabel,
            'badge_color' => $badgeColor,
            'description' => 'Ringkasan operasional pondok Anda tampil dari data tenant yang sedang aktif.',
            'meta' => $meta,
        ];
    }

    protected function buildTenantLifecycleNotice(?Tenant $tenant, bool $isSuperAdmin): ?array
    {
        if ($isSuperAdmin || ! $tenant) {
            return null;
        }

        $trialWarningDays = (int) config('saas.trial_warning_days', 3);
        $graceWarningDays = (int) config('saas.grace_warning_days', 3);

        if ($tenant->subscription_status === Tenant::SUBSCRIPTION_TRIAL && $tenant->trial_ends_at) {
            $remainingDays = max(0, now()->diffInDays($tenant->trial_ends_at, false));

            if ($remainingDays <= $trialWarningDays) {
                return [
                    'style' => 'warning',
                    'title' => 'Masa trial akan segera berakhir',
                    'message' => 'Tenant Anda masih dalam masa trial dan akan berakhir pada '.$tenant->trial_ends_at->translatedFormat('d M Y H:i').'.',
                    'action' => 'Segera hubungi admin platform untuk aktivasi subscription agar operasional pondok tidak terhenti.',
                ];
            }
        }

        if ($tenant->subscription_status === Tenant::SUBSCRIPTION_GRACE && $tenant->grace_ends_at) {
            $remainingDays = max(0, now()->diffInDays($tenant->grace_ends_at, false));

            return [
                'style' => $remainingDays <= $graceWarningDays ? 'danger' : 'warning',
                'title' => 'Tenant sedang dalam grace period',
                'message' => 'Akses tenant Anda masih dibuka sementara sampai '.$tenant->grace_ends_at->translatedFormat('d M Y H:i').'.',
                'action' => 'Konfirmasi pembayaran atau minta perpanjangan ke admin platform sebelum masa toleransi ini habis.',
            ];
        }

        return null;
    }
}
