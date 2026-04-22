<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">SaaS</div>
            <h2 class="page-title mt-1">Riwayat Subscription</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Log Subscription Tenant</h3>
                    </div>
                </div>
                <div class="card-body border-bottom user-filter-panel">
                    <form method="GET" action="{{ route('saas.subscription-histories.index') }}" class="row g-3 align-items-end user-filter-form">
                            <div class="col-lg-4 col-xl-4">
                                <label for="subscription-history-search" class="form-label">Cari Riwayat</label>
                                <input
                                    id="subscription-history-search"
                                    name="search"
                                    type="text"
                                    class="form-control"
                                    value="{{ $filters['search'] ?? '' }}"
                                    placeholder="Cari tenant, catatan admin, atau admin pengubah"
                                >
                            </div>
                            <div class="col-md-6 col-lg-2 col-xl-2">
                                <label for="subscription-history-tenant" class="form-label">Tenant</label>
                                <select id="subscription-history-tenant" name="tenant_id" class="form-select form-select-pretty">
                                    <option value="">Semua tenant</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}" @selected((string) ($filters['tenant_id'] ?? '') === (string) $tenant->id)>{{ $tenant->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 col-xl-2">
                                <label for="subscription-history-action" class="form-label">Aksi</label>
                                <select id="subscription-history-action" name="action" class="form-select form-select-pretty">
                                    <option value="">Semua aksi</option>
                                    <option value="activate_trial" @selected(($filters['action'] ?? '') === 'activate_trial')>Activate Trial</option>
                                    <option value="extend_trial" @selected(($filters['action'] ?? '') === 'extend_trial')>Extend Trial</option>
                                    <option value="activate_subscription" @selected(($filters['action'] ?? '') === 'activate_subscription')>Activate Subscription</option>
                                    <option value="mark_grace" @selected(($filters['action'] ?? '') === 'mark_grace')>Mark Grace</option>
                                    <option value="mark_expired" @selected(($filters['action'] ?? '') === 'mark_expired')>Mark Expired</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-lg-3 col-xl-2">
                                <div class="d-flex gap-2 user-filter-actions">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('saas.subscription-histories.index') }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                                <th>Periode</th>
                                <th>Catatan Admin</th>
                                <th>Diubah Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($histories as $history)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $history->tenant?->name ?? 'Tenant tidak ditemukan' }}</div>
                                        <div class="text-secondary small">{{ $history->tenant?->slug ?? '-' }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $history->created_at?->translatedFormat('d M Y H:i:s') ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-azure-lt text-azure">{{ str($history->action)->headline() }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $history->period_starts_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                                        <div class="text-secondary small mt-1">sampai {{ $history->period_ends_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $history->admin_note ?: 'Tanpa catatan admin' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $history->changedByUser?->name ?? 'Sistem' }}</div>
                                        <div class="text-secondary small">{{ $history->changedByUser?->username ? '@'.$history->changedByUser->username : 'Tanpa akun login' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada riwayat subscription yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($histories->hasPages())
                    <div class="card-footer">
                        {{ $histories->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
