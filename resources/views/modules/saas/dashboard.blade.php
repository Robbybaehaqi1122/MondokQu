@php
    use App\Models\Tenant;

    $statusUi = [
        Tenant::SUBSCRIPTION_ACTIVE => [
            'label' => 'Active',
            'meta' => 'Subscription berjalan',
            'indicator' => 'tenant-subscription-indicator-active',
        ],
        Tenant::SUBSCRIPTION_TRIAL => [
            'label' => 'Trial',
            'meta' => 'Perlu follow-up onboarding',
            'indicator' => 'tenant-subscription-indicator-trial',
        ],
        Tenant::SUBSCRIPTION_GRACE => [
            'label' => 'Grace',
            'meta' => 'Masa tenggang pembayaran',
            'indicator' => 'tenant-subscription-indicator-grace',
        ],
        Tenant::SUBSCRIPTION_EXPIRED => [
            'label' => 'Expired',
            'meta' => 'Prioritas follow-up',
            'indicator' => 'tenant-subscription-indicator-expired',
        ],
        Tenant::SUBSCRIPTION_DELETING => [
            'label' => 'Deleting',
            'meta' => 'Dalam antrean hapus',
            'indicator' => 'tenant-subscription-indicator-deleting',
        ],
    ];

    $formatCurrency = fn ($amount) => 'Rp '.number_format($amount / 100, 0, ',', '.');
    $formatCompactCurrency = function ($amount): string {
        $amount = $amount / 100;

        if ($amount >= 1000000000) {
            return 'Rp '.number_format($amount / 1000000000, 1, ',', '.').' M';
        }

        if ($amount >= 1000000) {
            return 'Rp '.number_format($amount / 1000000, 1, ',', '.').' jt';
        }

        if ($amount >= 1000) {
            return 'Rp '.number_format($amount / 1000, 0, ',', '.').' rb';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    };

    $maxNewTenants = max((int) collect($tenantGrowthChart)->max('new_tenants'), 1);
    $maxRevenue = max(collect($revenueChart)->max('amount'), 1);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">Platform</div>
            <h2 class="page-title mt-1">SaaS Dashboard</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card saas-stat-card-revenue">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Pendapatan</div>
                            <div class="fs-2 fw-bold mb-0">{{ $formatCurrency($stats['total_revenue']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Pendapatan Bulan Ini</div>
                            <div class="fs-2 fw-bold mb-0">{{ $formatCurrency($stats['revenue_this_month']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Tenant</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($stats['total_tenants']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Tenant Baru Bulan Ini</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($stats['new_tenants_this_month']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card saas-stat-card-trial">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Trial</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($stats['trial_tenants']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card saas-stat-card-active">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Active</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($stats['active_tenants']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card saas-stat-card-expired">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Expired</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($stats['expired_tenants']) }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card saas-stat-card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total User</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($stats['platform_users']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Pertumbuhan Tenant</h3>
                        <div class="text-secondary small">Tenant baru dan total tenant berjalan dalam 6 bulan terakhir.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="saas-chart-scroll">
                        <div class="saas-chart-bars saas-chart-bars-growth">
                            @foreach ($tenantGrowthChart as $point)
                                @php
                                    $height = $point['new_tenants'] > 0
                                        ? max(12, (int) round(($point['new_tenants'] / $maxNewTenants) * 100))
                                        : 3;
                                @endphp
                                <div
                                    class="saas-chart-column"
                                    title="{{ $point['label'] }}: {{ number_format($point['new_tenants']) }} tenant baru, total {{ number_format($point['total_tenants']) }}"
                                >
                                    <div class="saas-chart-value">{{ number_format($point['new_tenants']) }}</div>
                                    <div class="saas-chart-bar-shell">
                                        <div class="saas-chart-bar saas-chart-bar-primary" style="height: {{ $height }}%;"></div>
                                    </div>
                                    <div class="saas-chart-label">{{ $point['label'] }}</div>
                                    <div class="saas-chart-subvalue">Total {{ number_format($point['total_tenants']) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Pendapatan Platform</h3>
                        <div class="text-secondary small">Akumulasi pembayaran tenant dari billing notes.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="saas-revenue-summary mb-3">
                        <div>
                            <div class="text-secondary small text-uppercase fw-bold">Total Revenue</div>
                            <div class="fs-2 fw-bold">{{ $formatCurrency($stats['total_revenue']) }}</div>
                        </div>
                        <i class="ti ti-cash-banknote"></i>
                    </div>
                    <div class="saas-chart-scroll">
                        <div class="saas-chart-bars saas-chart-bars-revenue">
                            @foreach ($revenueChart as $point)
                                @php
                                    $height = $point['amount'] > 0
                                        ? max(12, (int) round(($point['amount'] / $maxRevenue) * 100))
                                        : 3;
                                @endphp
                                <div
                                    class="saas-chart-column"
                                    title="{{ $point['label'] }}: {{ $formatCurrency($point['amount']) }}"
                                >
                                    <div class="saas-chart-value">{{ $formatCompactCurrency($point['amount']) }}</div>
                                    <div class="saas-chart-bar-shell">
                                        <div class="saas-chart-bar saas-chart-bar-revenue" style="height: {{ $height }}%;"></div>
                                    </div>
                                    <div class="saas-chart-label">{{ $point['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Daftar Tenant</h3>
                        <div class="text-secondary small">Pondasi modul SaaS untuk pengelolaan trial, langganan, dan billing tenant.</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Batas Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $tenant)
                                @php
                                    $status = $statusUi[$tenant->subscription_status] ?? [
                                        'label' => str($tenant->subscription_status)->headline(),
                                        'meta' => 'Status belum dikenal',
                                        'indicator' => 'tenant-subscription-indicator-unknown',
                                    ];
                                    $accessLimit = match ($tenant->subscription_status) {
                                        Tenant::SUBSCRIPTION_TRIAL => [
                                            'label' => 'Trial ends',
                                            'value' => $tenant->trial_ends_at,
                                        ],
                                        Tenant::SUBSCRIPTION_ACTIVE => [
                                            'label' => 'Subscription ends',
                                            'value' => $tenant->subscription_ends_at,
                                        ],
                                        Tenant::SUBSCRIPTION_GRACE => [
                                            'label' => 'Grace ends',
                                            'value' => $tenant->grace_ends_at,
                                        ],
                                        Tenant::SUBSCRIPTION_EXPIRED => [
                                            'label' => 'Expired',
                                            'value' => $tenant->subscription_ends_at ?? $tenant->trial_ends_at,
                                        ],
                                        default => [
                                            'label' => 'Tidak ada batas aktif',
                                            'value' => null,
                                        ],
                                    };
                                @endphp
                                <tr class="tenant-status-row tenant-status-row-{{ $tenant->subscription_status }}">
                                    <td>
                                        <div class="fw-semibold">{{ $tenant->name }}</div>
                                        <div class="text-secondary small mt-1">{{ $tenant->slug }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $tenant->owner?->name ?? 'Belum ada owner' }}</div>
                                        <div class="text-secondary small mt-1">{{ $tenant->contact_email ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <div class="tenant-subscription-indicator {{ $status['indicator'] }}">
                                            <span class="tenant-subscription-dot"></span>
                                            <span class="tenant-subscription-copy">
                                                <span class="tenant-subscription-label">{{ $status['label'] }}</span>
                                                <span class="tenant-subscription-meta">{{ $status['meta'] }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $accessLimit['value']?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                                        <div class="text-secondary small mt-1">{{ $accessLimit['label'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary">Belum ada tenant. Modul SaaS siap dipakai untuk tahap provisioning tenant berikutnya.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($tenants->hasPages())
                    <div class="card-footer">
                        {{ $tenants->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
