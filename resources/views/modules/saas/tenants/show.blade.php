<x-app-layout>
    @php
        $statusBadgeClass = match ($tenant->subscription_status) {
            \App\Models\Tenant::SUBSCRIPTION_ACTIVE => 'bg-success-lt text-success',
            \App\Models\Tenant::SUBSCRIPTION_TRIAL => 'bg-azure-lt text-azure',
            \App\Models\Tenant::SUBSCRIPTION_GRACE => 'bg-warning-lt text-warning',
            \App\Models\Tenant::SUBSCRIPTION_EXPIRED => 'bg-danger-lt text-danger',
            \App\Models\Tenant::SUBSCRIPTION_DELETING => 'bg-danger text-white',
            default => 'bg-secondary-lt text-secondary',
        };
    @endphp

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
                                <span class="badge {{ $statusBadgeClass }}">{{ str($tenant->subscription_status)->headline() }}</span>
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
                        <div class="col-12">
                            <hr class="my-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-secondary small text-uppercase fw-bold">Kapasitas Tenant</div>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCapacityModal">
                                    <i class="ti ti-edit me-1"></i>Edit Kapasitas
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="text-secondary small">Maks User</div>
                                    <div class="fw-semibold">{{ $tenant->getMaxUsers() }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-secondary small">Maks Santri</div>
                                    <div class="fw-semibold">{{ $tenant->getMaxSantri() }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-secondary small">Maks Storage</div>
                                    <div class="fw-semibold">{{ $tenant->getMaxStorageMb() }} MB</div>
                                </div>
                            </div>
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
                                <th class="w-1">Aksi</th>
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
                                    <td>
                                        @if (! $user->isSuperAdmin() && $user->status === \App\Models\User::STATUS_ACTIVE)
                                            <form method="POST" action="{{ route('saas.tenants.users.impersonate', [$tenant, $user]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirm('Login sebagai {{ $user->name }} untuk troubleshooting tenant ini?')">
                                                    <i class="ti ti-login-2 me-1"></i>
                                                    Login sebagai
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-secondary small">Tidak tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada user yang terhubung ke tenant ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <div class="modal modal-blur fade" id="editCapacityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('saas.tenants.update-capacity', $tenant) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Kapasitas Tenant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="max_users" class="form-label">Maks User</label>
                                <input
                                    id="max_users"
                                    name="max_users"
                                    type="number"
                                    min="1"
                                    class="form-control"
                                    value="{{ $tenant->getMaxUsers() }}"
                                >
                                <div class="form-hint mt-2">Jumlah maksimal akun user.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="max_santri" class="form-label">Maks Santri</label>
                                <input
                                    id="max_santri"
                                    name="max_santri"
                                    type="number"
                                    min="1"
                                    class="form-control"
                                    value="{{ $tenant->getMaxSantri() }}"
                                >
                                <div class="form-hint mt-2">Jumlah maksimal data santri.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="max_storage_mb" class="form-label">Maks Storage (MB)</label>
                                <input
                                    id="max_storage_mb"
                                    name="max_storage_mb"
                                    type="number"
                                    min="1"
                                    class="form-control"
                                    value="{{ $tenant->getMaxStorageMb() }}"
                                >
                                <div class="form-hint mt-2">Batas upload file.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
