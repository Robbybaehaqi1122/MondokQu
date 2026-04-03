<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Manajemen User</h2>
            <div class="text-secondary mt-1">Kelola pembuatan akun, assignment role, dan reset password dari satu panel admin.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Tambah User Baru</h3>
                        <p class="text-secondary mb-0">Superadmin yang bisa membuat akun baru dan menentukan role-nya.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Nama</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="form-control @if($errors->createUser->has('name')) is-invalid @endif"
                                    value="{{ old('name') }}"
                                    required
                                >
                                @if ($errors->createUser->has('name'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('name') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input
                                    id="username"
                                    name="username"
                                    type="text"
                                    class="form-control @if($errors->createUser->has('username')) is-invalid @endif"
                                    value="{{ old('username') }}"
                                    required
                                >
                                @if ($errors->createUser->has('username'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('username') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select
                                    id="role"
                                    name="role"
                                    class="form-select form-select-pretty @if($errors->createUser->has('role')) is-invalid @endif"
                                    required
                                >
                                    <option value="">Pilih role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->createUser->has('role'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('role') }}</div>
                                @endif
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="form-control @if($errors->createUser->has('email')) is-invalid @endif"
                                    value="{{ old('email') }}"
                                    required
                                >
                                @if ($errors->createUser->has('email'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('email') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password Awal</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="form-control @if($errors->createUser->has('password')) is-invalid @endif"
                                    required
                                >
                                @if ($errors->createUser->has('password'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('password') }}</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    class="form-control @if($errors->createUser->has('password_confirmation')) is-invalid @endif"
                                    required
                                >
                                @if ($errors->createUser->has('password_confirmation'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('password_confirmation') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
                            <button type="submit" class="btn btn-primary px-4 py-2 text-nowrap">Tambah User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Daftar User</h3>
                        <p class="text-secondary mb-0">Atur role, reset password, hapus akun</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status Email</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $managedUser)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $managedUser->name }}</div>
                                        <div class="text-secondary small">{{ '@'.$managedUser->username }}</div>
                                        <div class="text-secondary small">{{ $managedUser->email }}</div>
                                    </td>
                                    <td class="user-role-cell">
                                        <form method="POST" action="{{ route('admin.users.update-role', $managedUser) }}" class="user-role-form">
                                            @csrf
                                            @method('PATCH')
                                            <div class="input-group user-role-group">
                                                <select name="role" class="form-select form-select-pretty user-role-select">
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}" @selected($managedUser->roles->contains('name', $role->name))>
                                                            {{ $role->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-outline-primary user-role-submit">
                                                    Simpan Role
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        @if ($managedUser->email_verified_at)
                                            <span class="badge bg-success-lt text-success">Terverifikasi</span>
                                        @else
                                            <span class="badge bg-warning-lt text-warning">Belum verifikasi</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle user-action-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end p-3 user-action-menu">
                                                <button
                                                    type="button"
                                                    class="btn btn-primary btn-sm w-100"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#resetPasswordModal{{ $managedUser->id }}"
                                                >
                                                    Reset Password
                                                </button>

                                                <div class="dropdown-divider my-3"></div>

                                                <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" @disabled(auth()->id() === $managedUser->id)>
                                                        Hapus Akun
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="modal modal-blur fade" id="resetPasswordModal{{ $managedUser->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('admin.users.update-password', $managedUser) }}">
                                                        @csrf
                                                        @method('PATCH')

                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reset Password</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <p class="text-secondary mb-3">
                                                                Atur password baru untuk <strong>{{ $managedUser->name }}</strong>.
                                                            </p>

                                                            <div class="mb-3">
                                                                <label for="password_{{ $managedUser->id }}" class="form-label">Password Baru</label>
                                                                <div class="login-field-wrapper login-field-wrapper-password">
                                                                    <input
                                                                        id="password_{{ $managedUser->id }}"
                                                                        name="password"
                                                                        type="password"
                                                                        class="form-control pe-6 js-reset-password-input"
                                                                        required
                                                                    >
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-icon btn-ghost-secondary login-password-toggle"
                                                                        data-password-toggle
                                                                        data-target="password_{{ $managedUser->id }}"
                                                                        aria-label="Tampilkan password"
                                                                        aria-pressed="false"
                                                                    >
                                                                        <i class="ti ti-eye"></i>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <label for="password_confirmation_{{ $managedUser->id }}" class="form-label">Konfirmasi Password</label>
                                                                <div class="login-field-wrapper login-field-wrapper-password">
                                                                    <input
                                                                        id="password_confirmation_{{ $managedUser->id }}"
                                                                        name="password_confirmation"
                                                                        type="password"
                                                                        class="form-control pe-6 js-reset-password-input"
                                                                        required
                                                                    >
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-icon btn-ghost-secondary login-password-toggle"
                                                                        data-password-toggle
                                                                        data-target="password_confirmation_{{ $managedUser->id }}"
                                                                        aria-label="Tampilkan password"
                                                                        aria-pressed="false"
                                                                    >
                                                                        <i class="ti ti-eye"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                                                                Batal
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                Simpan Password
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-secondary">Belum ada user selain seed/factory yang tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
                toggleButton.addEventListener('click', () => {
                    const targetId = toggleButton.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = toggleButton.querySelector('i');

                    if (! input || ! icon) {
                        return;
                    }

                    const isVisible = input.type === 'text';

                    input.type = isVisible ? 'password' : 'text';
                    toggleButton.setAttribute('aria-pressed', String(! isVisible));
                    toggleButton.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
                    icon.className = isVisible ? 'ti ti-eye' : 'ti ti-eye-off';
                });
            });
        });
    </script>
</x-app-layout>
