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

    $usageColor = fn ($pct) => match (true) {
        $pct >= 100 => 'bg-danger',
        $pct >= 80 => 'bg-warning',
        $pct >= 50 => 'bg-info',
        default => 'bg-success',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Resource Report</h2>
            <div class="text-secondary mt-1">
                Report pemanfaatan resource per tenant — user, santri, dan storage.
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Tenant</div>
                            <div class="h1 mb-1">{{ number_format($summary['total_tenants']) }}</div>
                            <div class="text-secondary small">Tenant terdaftar di platform.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Mendekati Batas (&ge;80%)</div>
                            <div class="h1 mb-1 @if ($summary['near_limit'] > 0) text-warning @endif">{{ number_format($summary['near_limit']) }}</div>
                            <div class="text-secondary small">Tenant dengan kapasitas hampir penuh.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Pemanfaatan Resource per Tenant</h3>
                        <div class="text-secondary small">Progress bar menunjukkan persentase pemakaian terhadap kapasitas maksimum.</div>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('saas.resource-report') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="r-search" class="form-label">Cari Tenant</label>
                            <input id="r-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Nama atau slug tenant">
                        </div>
                        <div class="col-md-3">
                            <label for="r-status" class="form-label">Status</label>
                            <select id="r-status" name="status" class="form-select">
                                <option value="">Semua status</option>
                                @foreach (Tenant::subscriptionStatuses() as $status)
                                    <option value="{{ $status }}" @selected($statusFilter === $status)>{{ str($status)->headline() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>
                                Terapkan
                            </button>
                            <a href="{{ route('saas.resource-report') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Status</th>
                                <th>User</th>
                                <th>Santri</th>
                                <th>Storage</th>
                                <th>Subscription Expired</th>
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

                                    $usersPct = $tenant->getUsagePercentage('users');
                                    $santriPct = $tenant->getUsagePercentage('santri');
                                    $storagePct = $tenant->getUsagePercentage('storage');

                                    $userCount = $tenant->users_count;
                                    $santriCount = $tenant->santris_count;
                                    $maxUsers = $tenant->getMaxUsers();
                                    $maxSantri = $tenant->getMaxSantri();
                                    $maxStorageMb = $tenant->getMaxStorageMb();

                                    $storageBytes = $tenant->getCurrentStorageBytes();
                                    $storageFormatted = $storageBytes >= 1073741824
                                        ? number_format($storageBytes / 1073741824, 2) . ' GB'
                                        : ($storageBytes >= 1048576
                                            ? number_format($storageBytes / 1048576, 1) . ' MB'
                                            : number_format($storageBytes / 1024, 1) . ' KB');

                                    $nearLimit = $usersPct >= 80 || $santriPct >= 80 || $storagePct >= 80;

                                    $expiredLabel = match ($tenant->subscription_status) {
                                        Tenant::SUBSCRIPTION_TRIAL => $tenant->trial_ends_at?->translatedFormat('d M Y'),
                                        Tenant::SUBSCRIPTION_ACTIVE => $tenant->subscription_ends_at?->translatedFormat('d M Y'),
                                        Tenant::SUBSCRIPTION_GRACE => $tenant->grace_ends_at?->translatedFormat('d M Y'),
                                        Tenant::SUBSCRIPTION_EXPIRED => ($tenant->subscription_ends_at ?? $tenant->trial_ends_at)?->translatedFormat('d M Y'),
                                        default => '-',
                                    };
                                @endphp
                                <tr class="@if ($nearLimit) table-warning @endif">
                                    <td>
                                        <div class="fw-semibold">{{ $tenant->name }}</div>
                                        <div class="text-secondary small">{{ $tenant->slug }}</div>
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
                                    <td style="min-width:180px">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>{{ $userCount }} / {{ $maxUsers }}</span>
                                            <span class="text-secondary small">{{ $usersPct }}%</span>
                                        </div>
                                        <div class="progress" style="height:8px">
                                            <div
                                                class="progress-bar {{ $usageColor($usersPct) }}"
                                                style="width: {{ min($usersPct, 100) }}%"
                                                role="progressbar"
                                                aria-valuenow="{{ $usersPct }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            ></div>
                                        </div>
                                    </td>
                                    <td style="min-width:180px">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>{{ $santriCount }} / {{ $maxSantri }}</span>
                                            <span class="text-secondary small">{{ $santriPct }}%</span>
                                        </div>
                                        <div class="progress" style="height:8px">
                                            <div
                                                class="progress-bar {{ $usageColor($santriPct) }}"
                                                style="width: {{ min($santriPct, 100) }}%"
                                                role="progressbar"
                                                aria-valuenow="{{ $santriPct }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            ></div>
                                        </div>
                                    </td>
                                    <td style="min-width:180px">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>{{ $storageFormatted }} / {{ $maxStorageMb }} MB</span>
                                            <span class="text-secondary small">{{ $storagePct }}%</span>
                                        </div>
                                        <div class="progress" style="height:8px">
                                            <div
                                                class="progress-bar {{ $usageColor($storagePct) }}"
                                                style="width: {{ min($storagePct, 100) }}%"
                                                role="progressbar"
                                                aria-valuenow="{{ $storagePct }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            ></div>
                                        </div>
                                    </td>
                                    <td class="text-secondary small text-nowrap">
                                        @if ($nearLimit)
                                            <span class="badge bg-warning-lt text-warning me-1">
                                                <i class="ti ti-alert-triangle"></i>
                                                Mendekati batas
                                            </span>
                                        @endif
                                        <div>{{ $expiredLabel }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada tenant.</td>
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
