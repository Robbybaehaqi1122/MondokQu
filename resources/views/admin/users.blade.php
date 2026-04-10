<x-app-layout>
    @php
        $currentUser = auth()->user();
        $statusLabels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
        ];

        $roleBadgeClasses = [
            'Superadmin' => 'bg-danger-lt text-danger',
            'Admin' => 'bg-azure-lt text-azure',
            'Bendahara' => 'bg-green-lt text-green',
            'Pengurus' => 'bg-yellow-lt text-yellow',
            'Musyrif/Ustadz' => 'bg-purple-lt text-purple',
            'Wali Santri' => 'bg-indigo-lt text-indigo',
        ];

        $statusBadgeClasses = [
            'active' => 'bg-success-lt text-success',
            'inactive' => 'bg-secondary-lt text-secondary',
            'suspended' => 'bg-danger-lt text-danger',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Manajemen User</h2>
            <div class="text-secondary mt-1">Kelola akun pengguna, profil user, status akun, dan reset password.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Daftar User</h3>
                            <div class="text-secondary small mt-2">
                                Menampilkan {{ $users->total() }} dari total {{ $allUsersCount }} user.
                            </div>
                            @unless ($canManageRoles)
                                <div class="text-secondary small mt-2">Pengaturan role dipisahkan ke menu khusus dan perubahan role dibatasi untuk Superadmin.</div>
                            @endunless
                        </div>

                        <div class="d-flex align-items-center">
                            <button
                                type="button"
                                class="btn btn-primary"
                                id="open-create-user-modal"
                                data-bs-toggle="modal"
                                data-bs-target="#createUserModal"
                            >
                                <i class="ti ti-user-plus me-1"></i>
                                Tambah User
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.users') }}" class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label for="q" class="form-label">Cari User</label>
                            <input
                                id="q"
                                name="q"
                                type="text"
                                class="form-control"
                                value="{{ $filters['q'] }}"
                                placeholder="Cari nama, username, atau email"
                            >
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label for="role_filter" class="form-label">Role</label>
                            <select id="role_filter" name="role" class="form-select form-select-pretty">
                                <option value="">Semua Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected($filters['role'] === $role->name)>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label for="status_filter" class="form-label">Status</label>
                            <select id="status_filter" name="status" class="form-select form-select-pretty">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" @selected($filters['status'] === $status)>
                                        {{ $statusLabels[$status] ?? ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <label for="verification_filter" class="form-label">Verifikasi</label>
                            <select id="verification_filter" name="verification" class="form-select form-select-pretty">
                                <option value="">Semua</option>
                                <option value="verified" @selected($filters['verification'] === 'verified')>Verified</option>
                                <option value="unverified" @selected($filters['verification'] === 'unverified')>Unverified</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Email Verification</th>
                                <th>Last Login</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $managedUser)
                                @php
                                    $canManageTargetUser = $currentUser?->isSuperAdmin() || ! $managedUser->isSuperAdmin();
                                    $canDeleteUser = $currentUser?->isSuperAdmin() && $currentUser->id !== $managedUser->id;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $managedUser->name }}</div>
                                        <div class="text-secondary small mt-2">
                                            Dibuat oleh:
                                            <span class="fw-medium">
                                                {{ $managedUser->creator?->name ?? 'System / Seeder' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="user-role-cell">
                                        <div class="user-control-card user-control-card-role">
                                            <div class="user-control-summary">
                                                @forelse ($managedUser->roles as $managedRole)
                                                    <span class="badge {{ $roleBadgeClasses[$managedRole->name] ?? 'bg-azure-lt text-azure' }}">
                                                        {{ $managedRole->name }}
                                                    </span>
                                                @empty
                                                    <span class="badge bg-secondary-lt text-secondary">Tanpa role</span>
                                                @endforelse
                                            </div>

                                            @if ($canManageRoles)
                                                <form method="POST" action="{{ route('admin.users.update-role', $managedUser) }}" class="user-control-form js-role-update-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="user-control-stack">
                                                        <select name="role" class="form-select form-select-pretty user-control-select">
                                                            @foreach ($roles as $role)
                                                                <option value="{{ $role->name }}" @selected($managedUser->roles->contains('name', $role->name))>
                                                                    {{ $role->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-outline-primary user-control-submit">
                                                            Simpan Role
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="user-control-locked">
                                                    <i class="ti ti-lock"></i>
                                                    <span>Hanya Superadmin yang dapat mengubah role.</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="user-status-cell">
                                        <div class="user-control-card user-control-card-status">
                                            <div class="user-control-summary">
                                                <span class="badge {{ $statusBadgeClasses[$managedUser->status] ?? 'bg-secondary-lt text-secondary' }}">
                                                    {{ $statusLabels[$managedUser->status] ?? ucfirst($managedUser->status) }}
                                                </span>
                                            </div>

                                            @if ($canManageTargetUser)
                                                <form method="POST" action="{{ route('admin.users.update-status', $managedUser) }}" class="user-control-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="user-control-stack">
                                                        <select name="status" class="form-select form-select-pretty user-control-select">
                                                            @foreach ($statuses as $status)
                                                                <option value="{{ $status }}" @selected($managedUser->status === $status)>
                                                                    {{ $statusLabels[$status] ?? ucfirst($status) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-outline-primary user-control-submit" @disabled($currentUser?->id === $managedUser->id)>
                                                            Simpan Status
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="user-control-locked">
                                                    <i class="ti ti-shield-lock"></i>
                                                    <span>Akun Superadmin hanya bisa diubah oleh Superadmin.</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if ($managedUser->email_verified_at)
                                            <span class="badge bg-success-lt text-success">Terverifikasi</span>
                                            <div class="text-secondary small mt-2">
                                                {{ $managedUser->email_verified_at->translatedFormat('d M Y H:i') }}
                                            </div>
                                        @else
                                            <span class="badge bg-warning-lt text-warning">Belum verifikasi</span>
                                            <div class="text-secondary small mt-2">
                                                Menunggu klik link verifikasi dari email user.
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($managedUser->last_login_at)
                                            <span class="badge bg-blue-lt text-blue">Sudah login</span>
                                            <div class="text-secondary small mt-2">
                                                {{ $managedUser->last_login_at->translatedFormat('d M Y H:i') }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-lt text-secondary">Belum pernah login</span>
                                            <div class="text-secondary small mt-2">
                                                Menunggu login pertama user.
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle user-action-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end p-3 user-action-menu">
                                                <a href="{{ route('admin.users.show', $managedUser) }}" class="btn btn-outline-secondary btn-sm w-100">
                                                    Lihat Detail User
                                                </a>

                                                <div class="dropdown-divider my-3"></div>

                                                @if (! $managedUser->hasVerifiedEmail())
                                                    @if ($canManageTargetUser)
                                                        <form method="POST" action="{{ route('admin.users.resend-verification', $managedUser) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                                                Kirim Ulang Verifikasi
                                                            </button>
                                                        </form>

                                                        <div class="dropdown-divider my-3"></div>

                                                        <form method="POST" action="{{ route('admin.users.verify-email', $managedUser) }}" onsubmit="return confirm('Tandai email user ini sebagai terverifikasi?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                                                Verifikasi Manual
                                                            </button>
                                                        </form>

                                                        <div class="dropdown-divider my-3"></div>
                                                    @endif
                                                @endif

                                                @if ($canManageTargetUser)
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary btn-sm w-100"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal{{ $managedUser->id }}"
                                                    >
                                                        Edit Profil
                                                    </button>

                                                    <div class="dropdown-divider my-3"></div>

                                                    <form method="POST" action="{{ route('admin.users.update-password', $managedUser) }}" onsubmit="return confirm('Reset password user ini ke password default? User akan diwajibkan ganti password saat login berikutnya.')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                                            Reset Password Default
                                                        </button>
                                                    </form>

                                                    @if ($currentUser?->isSuperAdmin())
                                                        <div class="dropdown-divider my-3"></div>
                                                    @endif
                                                @else
                                                    <div class="text-secondary small">
                                                        Aksi sensitif untuk akun Superadmin hanya tersedia bagi Superadmin.
                                                    </div>
                                                @endif

                                                @if ($currentUser?->isSuperAdmin())
                                                    <form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" @disabled(! $canDeleteUser)>
                                                            Hapus Akun
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($canManageTargetUser)
                                            <div class="modal modal-blur fade" id="editUserModal{{ $managedUser->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PATCH')

                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Profil User</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <p class="text-secondary mb-3">
                                                                    Perbarui identitas dasar untuk <strong>{{ $managedUser->name }}</strong>.
                                                                </p>

                                                                <div class="mb-3">
                                                                    <label for="edit_name_{{ $managedUser->id }}" class="form-label">Nama</label>
                                                                    <input
                                                                        id="edit_name_{{ $managedUser->id }}"
                                                                        name="name"
                                                                        type="text"
                                                                        class="form-control"
                                                                        value="{{ $managedUser->name }}"
                                                                        required
                                                                    >
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="edit_username_{{ $managedUser->id }}" class="form-label">Username</label>
                                                                    <input
                                                                        id="edit_username_{{ $managedUser->id }}"
                                                                        name="username"
                                                                        type="text"
                                                                        class="form-control"
                                                                        value="{{ $managedUser->username }}"
                                                                        required
                                                                    >
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="edit_email_{{ $managedUser->id }}" class="form-label">Email</label>
                                                                    <input
                                                                        id="edit_email_{{ $managedUser->id }}"
                                                                        name="email"
                                                                        type="email"
                                                                        class="form-control"
                                                                        value="{{ $managedUser->email }}"
                                                                        required
                                                                    >
                                                                    <div class="form-hint mt-2">Jika email diubah, status verifikasi email akan direset.</div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="edit_phone_number_{{ $managedUser->id }}" class="form-label">No. HP</label>
                                                                    <input
                                                                        id="edit_phone_number_{{ $managedUser->id }}"
                                                                        name="phone_number"
                                                                        type="text"
                                                                        class="form-control"
                                                                        value="{{ $managedUser->phone_number }}"
                                                                        placeholder="Contoh: 081234567890"
                                                                    >
                                                                </div>

                                                                <div>
                                                                    <label for="edit_avatar_{{ $managedUser->id }}" class="form-label">Upload Avatar</label>
                                                                    @if ($managedUser->avatarUrl())
                                                                        <div class="d-flex align-items-center gap-3 mb-3">
                                                                            <img src="{{ $managedUser->avatarUrl() }}" alt="Avatar {{ $managedUser->name }}" class="user-inline-avatar">
                                                                            <div class="text-secondary small">Avatar saat ini</div>
                                                                        </div>
                                                                    @endif
                                                                    <input
                                                                        id="edit_avatar_{{ $managedUser->id }}"
                                                                        name="avatar"
                                                                        type="file"
                                                                        class="form-control"
                                                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                                                    >
                                                                    <div class="form-hint mt-2">Hanya file gambar JPG, JPEG, PNG, atau WEBP. Dimensi minimal 200x200 px, maksimal 2000x2000 px, ukuran file maksimal 2 MB.</div>
                                                                </div>
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
                                        @endif

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada user selain seed/factory yang tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())
                    <div class="card-footer">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah User Baru</h5>
                            <div class="text-secondary small mt-1">Lengkapi identitas dasar, role awal, dan status akun user.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
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
                                @else
                                    <div class="form-hint mt-2">Harus unik dan mudah diingat user.</div>
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
                                    @foreach ($assignableRoles as $role)
                                        <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->createUser->has('role'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('role') }}</div>
                                @else
                                    <div class="form-hint mt-2">Role yang bisa dipilih mengikuti level akses akun Anda.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status User</label>
                                <select
                                    id="status"
                                    name="status"
                                    class="form-select form-select-pretty @if($errors->createUser->has('status')) is-invalid @endif"
                                    required
                                >
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected(old('status', 'active') === $status)>
                                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($errors->createUser->has('status'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('status') }}</div>
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
                                @else
                                    <div class="form-hint mt-2">Pastikan email belum terdaftar </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">No. HP</label>
                                <input
                                    id="phone_number"
                                    name="phone_number"
                                    type="text"
                                    class="form-control @if($errors->createUser->has('phone_number')) is-invalid @endif"
                                    value="{{ old('phone_number') }}"
                                    placeholder="Contoh: 081234567890"
                                >
                                @if ($errors->createUser->has('phone_number'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('phone_number') }}</div>
                                @else
                                    <div class="form-hint mt-2">Opsional. Dipakai untuk kontak user jika diperlukan.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="avatar" class="form-label">Upload Avatar</label>
                                <input
                                    id="avatar"
                                    name="avatar"
                                    type="file"
                                    class="form-control @if($errors->createUser->has('avatar')) is-invalid @endif"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                >
                                @if ($errors->createUser->has('avatar'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('avatar') }}</div>
                                @else
                                    <div class="form-hint mt-2">
                                        Opsional. Hanya file gambar JPG, JPEG, PNG, atau WEBP. Dimensi minimal 200x200 px, maksimal 2000x2000 px, ukuran file maksimal 2 MB.
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="password" class="form-label mb-0">Password Awal</label>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        id="generate-create-password"
                                    >
                                        Generate Password
                                    </button>
                                </div>
                                <div class="login-field-wrapper login-field-wrapper-password">
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        class="form-control pe-6 @if($errors->createUser->has('password')) is-invalid @endif"
                                        required
                                    >
                                    <button
                                        type="button"
                                        class="btn btn-icon btn-ghost-secondary login-password-toggle"
                                        data-password-toggle
                                        data-target="password"
                                        aria-label="Tampilkan password"
                                        aria-pressed="false"
                                    >
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                                @if ($errors->createUser->has('password'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('password') }}</div>
                                @else
                                    <div class="form-hint mt-2">Minimal 8 karakter.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <div class="login-field-wrapper login-field-wrapper-password">
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        class="form-control pe-6 @if($errors->createUser->has('password_confirmation')) is-invalid @endif"
                                        required
                                    >
                                    <button
                                        type="button"
                                        class="btn btn-icon btn-ghost-secondary login-password-toggle"
                                        data-password-toggle
                                        data-target="password_confirmation"
                                        aria-label="Tampilkan password"
                                        aria-pressed="false"
                                    >
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                                @if ($errors->createUser->has('password_confirmation'))
                                    <div class="invalid-feedback">{{ $errors->createUser->first('password_confirmation') }}</div>
                                @else
                                    <div class="form-hint mt-2">Harus sama dengan password awal.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>
                </form>
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

            document.querySelectorAll('.js-role-update-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const select = form.querySelector('select[name="role"]');
                    const selectedRole = select?.options[select.selectedIndex]?.text ?? 'role ini';

                    if (! window.confirm(`Yakin ingin mengubah role user menjadi ${selectedRole}?`)) {
                        event.preventDefault();
                    }
                });
            });

            document.getElementById('generate-create-password')?.addEventListener('click', () => {
                const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
                const generatedPassword = Array.from({ length: 12 }, () => alphabet[Math.floor(Math.random() * alphabet.length)]).join('');
                const passwordInput = document.getElementById('password');
                const confirmationInput = document.getElementById('password_confirmation');

                if (passwordInput && confirmationInput) {
                    passwordInput.value = generatedPassword;
                    confirmationInput.value = generatedPassword;
                    passwordInput.type = 'text';
                    confirmationInput.type = 'text';
                }
            });

            @if ($errors->createUser->any())
                document.getElementById('open-create-user-modal')?.click();
            @endif
        });
    </script>
</x-app-layout>
