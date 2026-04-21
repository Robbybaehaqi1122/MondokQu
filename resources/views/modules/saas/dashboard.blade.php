<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">Platform</div>
            <h2 class="page-title mt-1">SaaS Dashboard</h2>
            <div class="text-secondary mt-2">Pantau tenant pondok, status trial, dan kesiapan billing dari satu modul terpisah.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Tenant</div>
                            <div class="h1 mb-0">{{ $stats['total_tenants'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Trial</div>
                            <div class="h1 mb-0">{{ $stats['trial_tenants'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Active</div>
                            <div class="h1 mb-0">{{ $stats['active_tenants'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Grace</div>
                            <div class="h1 mb-0">{{ $stats['grace_tenants'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Expired</div>
                            <div class="h1 mb-0">{{ $stats['expired_tenants'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total User</div>
                            <div class="h1 mb-0">{{ $stats['platform_users'] }}</div>
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
                                <th>Trial Ends</th>
                                <th>Subscription Ends</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $tenant)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $tenant->name }}</div>
                                        <div class="text-secondary small mt-1">{{ $tenant->slug }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $tenant->owner?->name ?? 'Belum ada owner' }}</div>
                                        <div class="text-secondary small mt-1">{{ $tenant->contact_email ?: '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-azure-lt text-azure">{{ str($tenant->subscription_status)->headline() }}</span>
                                    </td>
                                    <td>{{ $tenant->trial_ends_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                    <td>{{ $tenant->subscription_ends_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Belum ada tenant. Modul SaaS siap dipakai untuk tahap provisioning tenant berikutnya.</td>
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
