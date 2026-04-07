<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Permission Management</h2>
            <div class="text-secondary mt-1">Petakan hak akses detail agar role tetap rapi dan kontrol sistem lebih fleksibel.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Daftar Permission</h3>
                            <p class="text-secondary mb-0">Permission adalah detail hak akses yang bisa dipetakan ke satu atau banyak role.</p>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            id="open-create-permission-modal"
                            data-bs-toggle="modal"
                            data-bs-target="#createPermissionModal"
                        >
                            <i class="ti ti-key me-1"></i>
                            Tambah Permission
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Permission</th>
                                <th>Kategori</th>
                                <th>Dipakai Oleh</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($permissions as $permission)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $permission->name }}</div>
                                        <div class="text-secondary small">Hak akses detail yang dapat dipetakan ke role terkait.</div>
                                    </td>
                                    <td class="text-secondary small">
                                        {{ str($permission->name)->before(' ')->headline() }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            @forelse ($permission->roles as $role)
                                                <span class="badge bg-indigo-lt text-indigo">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-secondary small">Belum dipakai role mana pun.</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPermissionModal{{ $permission->id }}"
                                            >
                                                Edit Nama
                                            </button>

                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#mapRolesModal{{ $permission->id }}"
                                            >
                                                Mapping Role
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary">Belum ada permission yang tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="createPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.permissions.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Permission Baru</h5>
                            <div class="text-secondary small mt-1">Gunakan nama yang jelas, misalnya <code>approve izin</code> atau <code>edit historical pembayaran</code>.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <label for="permission_name" class="form-label">Nama Permission</label>
                        <input
                            id="permission_name"
                            name="name"
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Contoh: approve tagihan"
                            required
                        >
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

    @foreach ($permissions as $permission)
        <div class="modal modal-blur fade" id="editPermissionModal{{ $permission->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Edit Nama Permission</h5>
                                <div class="text-secondary small mt-1">Perbarui nama permission agar lebih jelas dan konsisten.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <label for="permission_edit_name_{{ $permission->id }}" class="form-label">Nama Permission</label>
                            <input
                                id="permission_edit_name_{{ $permission->id }}"
                                name="name"
                                type="text"
                                class="form-control"
                                value="{{ $permission->name }}"
                                required
                            >
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal modal-blur fade" id="mapRolesModal{{ $permission->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.permissions.update-roles', $permission) }}">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Mapping Role untuk Permission</h5>
                                <div class="text-secondary small mt-1">{{ $permission->name }} - pilih role yang boleh memiliki permission ini.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                @foreach ($roles as $role)
                                    <div class="col-md-6">
                                        <label class="form-check role-mapping-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="roles[]"
                                                value="{{ $role->id }}"
                                                @checked($permission->roles->contains('id', $role->id))
                                            >
                                            <span class="form-check-label">
                                                <span class="fw-semibold d-block">{{ $role->name }}</span>
                                                <span class="text-secondary small">Role yang akan menerima permission ini.</span>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Simpan Mapping
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
                document.getElementById('open-create-permission-modal')?.click();
            @endif
        });
    </script>
</x-app-layout>
