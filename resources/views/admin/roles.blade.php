<x-app-layout>
    @php
        $roleDescriptions = [
            'Superadmin' => 'Akses penuh untuk user, role, permission, dan pengaturan sistem.',
            'Admin' => 'Mengelola operasional inti aplikasi tanpa akses penuh ke konfigurasi sistem.',
            'Pengurus' => 'Fokus pada data santri, kamar, dan proses izin harian.',
            'Bendahara' => 'Fokus pada pembayaran, transaksi, dan laporan keuangan.',
            'Musyrif/Ustadz' => 'Mendampingi tahfidz, absensi, dan pembinaan santri.',
            'Wali Santri' => 'Akses portal orang tua untuk memantau informasi santri.',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Manajemen Role</h2>
            <div class="text-secondary mt-1">Kelola struktur jabatan dan hak akses. Role <strong>Global</strong> menjadi template untuk role <strong>Per-Tenant</strong>.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        @if ($isSuperAdmin)
            {{-- Global Roles --}}
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                            <div>
                                <h3 class="card-title">
                                    <i class="ti ti-world me-1"></i>
                                    Role Global (Template)
                                </h3>
                                <p class="text-secondary mb-0">Role ini bersifat global — perubahan permission akan mempengaruhi <strong>semua user</strong> di <strong>seluruh tenant</strong>. Role ini juga menjadi template saat membuat role per-tenant.</p>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>User Terhubung</th>
                                    <th>Permission Aktif</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($globalRoles as $role)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $role->name }}</div>
                                            <div class="text-secondary small">
                                                {{ $roleDescriptions[$role->name] ?? 'Role jabatan operasional sistem.' }}
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge bg-muted-lt text-muted">Global</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-azure-lt text-azure">{{ $role->users_count }} user</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse ($role->permissions->take(4) as $permission)
                                                    <span class="badge bg-success-lt text-success">{{ $permission->name }}</span>
                                                @empty
                                                    <span class="text-secondary small">Belum ada permission.</span>
                                                @endforelse
                                            </div>
                                            @if ($role->permissions_count > 4)
                                                <div class="text-secondary small mt-2">+{{ $role->permissions_count - 4 }} permission lainnya</div>
                                            @endif
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#managePermissionsModal{{ $role->id }}"
                                            >
                                                Atur Permission
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-secondary">Belum ada role global yang tersimpan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Per-Tenant Roles --}}
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                            <div>
                                <h3 class="card-title">
                                    <i class="ti ti-building-community me-1"></i>
                                    Role Per-Tenant
                                </h3>
                                <p class="text-secondary mb-0">Role spesifik untuk masing-masing pondok. Perubahan hanya berdampak di tenant tersebut. Klik <strong>Sync dari Global</strong> untuk menyelaraskan permission dengan template terbaru.</p>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                @if (! $tenantRoles->isEmpty())
                                    <form method="POST" action="{{ route('admin.roles.sync-tenant-all') }}" class="d-inline"
                                        onsubmit="return confirm('Sinkronisasi semua role di semua tenant dengan template global? Permission yang sudah diubah manual akan ditimpa.')">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="ti ti-refresh me-1"></i>Sync Semua Tenant
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($tenantRoles->isEmpty())
                        <div class="card-body">
                            <div class="text-secondary">Belum ada tenant dengan role spesifik.</div>
                        </div>
                    @else
                        @foreach ($tenantRoles as $tenantName => $roles)
                            @php $tenantId = $roles->first()->tenant_id; @endphp
                            <details class="group" @if ($loop->first) open @endif>
                                <summary class="px-3 py-2 d-flex align-items-center gap-2 border-bottom cursor-pointer" style="list-style: none;">
                                    <i class="ti ti-chevron-right group-open:rotate-90 transition-transform"></i>
                                    <span class="fw-semibold">{{ $tenantName }}</span>
                                    <span class="badge bg-purple-lt text-purple ms-1">{{ $roles->count() }} role</span>
                                    <div class="ms-auto">
                                        <form method="POST" action="{{ route('admin.roles.sync-tenant', $tenantId) }}" class="d-inline"
                                            onsubmit="return confirm('Sinkronisasi semua role tenant ini dengan template global? Permission yang sudah diubah manual akan ditimpa.')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="ti ti-refresh me-1"></i>Sync Semua Role
                                            </button>
                                        </form>
                                    </div>
                                </summary>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table">
                                        <thead>
                                            <tr>
                                                <th>Role</th>
                                                <th>User Terhubung</th>
                                                <th>Permission Aktif</th>
                                                <th class="w-1">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($roles as $role)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $role->name }}</div>
                                                        <div class="text-secondary small">
                                                            {{ $roleDescriptions[$role->name] ?? 'Role jabatan operasional sistem.' }}
                                                        </div>
                                                        <div class="mt-1">
                                                            <span class="badge bg-purple-lt text-purple">Tenant</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-azure-lt text-azure">{{ $role->users_count }} user</span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @forelse ($role->permissions->take(4) as $permission)
                                                                <span class="badge bg-success-lt text-success">{{ $permission->name }}</span>
                                                            @empty
                                                                <span class="text-secondary small">Belum ada permission.</span>
                                                            @endforelse
                                                        </div>
                                                        @if ($role->permissions_count > 4)
                                                            <div class="text-secondary small mt-2">+{{ $role->permissions_count - 4 }} permission lainnya</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline-primary btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#managePermissionsModal{{ $role->id }}"
                                                            >
                                                                Atur Permission
                                                            </button>
                                                            <form method="POST" action="{{ route('admin.roles.sync-from-template', $role) }}" class="d-inline">
                                                                @csrf
                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-ghost-warning btn-sm"
                                                                    onclick="return confirm('Sync permission role {{ $role->name }} dari template global?')"
                                                                >
                                                                    Sync dari Global
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        @endforeach
                    @endif
                </div>
            </div>
        @else
            {{-- Tenant Admin View --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                            <div>
                                <h3 class="card-title">
                                    <i class="ti ti-building-community me-1"></i>
                                    Daftar Role — {{ optional(auth()->user()?->tenant)->name ?? 'Tenant Saya' }}
                                </h3>
                                <p class="text-secondary mb-0">Role di bawah ini spesifik untuk tenant Anda. Perubahan hanya berdampak di pondok ini.</p>
                            </div>

                            @can('manage system settings')
                            <button
                                type="button"
                                class="btn btn-primary"
                                id="open-create-role-modal"
                                data-bs-toggle="modal"
                                data-bs-target="#createRoleModal"
                            >
                                <i class="ti ti-user-plus me-1"></i>
                                Tambah Role
                            </button>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>User Terhubung</th>
                                    <th>Permission Aktif</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $role->name }}</div>
                                            <div class="text-secondary small">
                                                {{ $roleDescriptions[$role->name] ?? 'Role jabatan operasional sistem.' }}
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge bg-purple-lt text-purple">Tenant</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-azure-lt text-azure">{{ $role->users_count }} user</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse ($role->permissions->take(4) as $permission)
                                                    <span class="badge bg-success-lt text-success">{{ $permission->name }}</span>
                                                @empty
                                                    <span class="text-secondary small">Belum ada permission.</span>
                                                @endforelse
                                            </div>
                                            @if ($role->permissions_count > 4)
                                                <div class="text-secondary small mt-2">+{{ $role->permissions_count - 4 }} permission lainnya</div>
                                            @endif
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#managePermissionsModal{{ $role->id }}"
                                            >
                                                Atur Permission
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-secondary">Belum ada role yang tersimpan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Create Role Modal — only superadmin --}}
    @can('manage system settings')
    <div class="modal modal-blur fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Role Baru</h5>
                            <div class="text-secondary small mt-1">Buat role baru untuk jabatan atau kebutuhan operasional tertentu.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="role_name" class="form-label">Nama Role</label>
                            <input
                                id="role_name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Operator Pendaftaran"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-hint mt-2">Gunakan nama role yang merepresentasikan jabatan, bukan detail hak akses.</div>
                        </div>

                        @if ($isSuperAdmin)
                            <div class="mb-3">
                                <label for="role_tenant_id" class="form-label">Scope</label>
                                <select id="role_tenant_id" name="tenant_id" class="form-select">
                                    <option value="">Global (seluruh tenant)</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">Tenant: {{ $tenant->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint mt-2">Pilih <strong>Global</strong> untuk role yang berlaku di semua tenant, atau pilih tenant spesifik.</div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Simpan Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Permission Modals --}}
    @php
        $allRoles = $isSuperAdmin ? $globalRoles->concat($tenantRoles->flatten()) : $roles;
    @endphp
    @foreach ($allRoles as $role)
        <div class="modal modal-blur fade" id="managePermissionsModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Atur Permission Role</h5>
                                <div class="text-secondary small mt-1">{{ $role->name }} — pilih permission yang ingin diaktifkan.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @if ($role->tenant_id)
                                <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                                    <i class="ti ti-alert-triangle"></i>
                                    <span>
                                        <strong>Perhatian:</strong> Role ini spesifik untuk <strong>{{ $role->tenant?->name ?? 'tenant ini' }}</strong> — perubahan permission hanya akan mempengaruhi
                                        <strong>user</strong> dengan role <strong>{{ $role->name }}</strong> di tenant <strong>ini</strong>.
                                    </span>
                                </div>
                            @else
                                <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                                    <i class="ti ti-alert-triangle"></i>
                                    <span>
                                        <strong>Perhatian:</strong> Role bersifat <strong>global (template)</strong> — perubahan permission akan mempengaruhi
                                        <strong>semua user</strong> dengan role <strong>{{ $role->name }}</strong> di <strong>seluruh tenant</strong>.
                                    </span>
                                </div>
                            @endif

                            <div class="row g-3">
                                @foreach ($permissionGroups as $groupLabel => $groupPermissions)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="card role-permission-card h-100">
                                            <div class="card-header">
                                                <h3 class="card-title">{{ $groupLabel }}</h3>
                                            </div>
                                            <div class="card-body d-flex flex-column gap-2">
                                                @foreach ($groupPermissions as $permission)
                                                    <label class="form-check">
                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $permission->id }}"
                                                            @checked($role->permissions->contains('id', $permission->id))
                                                        >
                                                        <span class="form-check-label">{{ $permission->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Simpan Permission
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->has('name'))
                document.getElementById('open-create-role-modal')?.click();
            @endif
        });
    </script>
</x-app-layout>
