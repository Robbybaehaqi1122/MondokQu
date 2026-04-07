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
            <div class="text-secondary mt-1">Kelola struktur jabatan aplikasi, keterkaitan user, dan hak akses yang melekat pada setiap role.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Daftar Role</h3>
                            <p class="text-secondary mb-0">Role merepresentasikan jabatan. Permission di bawahnya menentukan detail hak akses setiap jabatan.</p>
                        </div>

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
    </div>

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
                        <div>
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

    @foreach ($roles as $role)
        <div class="modal modal-blur fade" id="managePermissionsModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Atur Permission Role</h5>
                                <div class="text-secondary small mt-1">{{ $role->name }} - pilih permission yang ingin diaktifkan.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
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
