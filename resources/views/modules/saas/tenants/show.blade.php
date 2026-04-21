<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">SaaS</div>
            <h2 class="page-title mt-1">{{ $tenant->name }}</h2>
            <div class="text-secondary mt-2">Detail tenant, status langganan, dan ringkasan penggunaan pondok.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Tenant</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Slug</div>
                            <div class="mt-1">{{ $tenant->slug }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Owner</div>
                            <div class="mt-1">{{ $tenant->owner?->name ?? 'Belum ada owner tenant' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Email Kontak</div>
                            <div class="mt-1">{{ $tenant->contact_email ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Nomor Kontak</div>
                            <div class="mt-1">{{ $tenant->contact_phone_number ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Plan</div>
                            <div class="mt-1">{{ str($tenant->subscription_plan)->headline() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Status</div>
                            <div class="mt-1">
                                <span class="badge bg-azure-lt text-azure">{{ str($tenant->subscription_status)->headline() }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Trial Ends At</div>
                            <div class="mt-1">{{ $tenant->trial_ends_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small text-uppercase fw-bold">Subscription Ends At</div>
                            <div class="mt-1">{{ $tenant->subscription_ends_at?->translatedFormat('d M Y H:i') ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Status Akses</div>
                            <div class="mt-2">
                                <span class="badge {{ $accessSummary['has_access'] ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' }}">
                                    {{ $accessSummary['access_label'] }}
                                </span>
                            </div>
                            <div class="text-secondary small mt-3">{{ $accessSummary['access_reason'] }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total User</div>
                            <div class="h1 mb-0">{{ $tenant->users_count }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Santri</div>
                            <div class="h1 mb-0">{{ $tenant->santris_count }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Activity Logs</div>
                            <div class="h1 mb-0">{{ $tenant->activity_logs_count }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Terbaru di Tenant</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentUsers as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ '@'.$user->username }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->getRoleNames()->implode(', ') ?: 'Tanpa role' }}</td>
                                    <td>{{ str($user->status)->headline() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-secondary">Belum ada user yang terhubung ke tenant ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
