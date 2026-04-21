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

        $primaryRole = $userDetail->roles->first()?->name;
        $isVerified = $userDetail->hasVerifiedEmail();
        $isActive = $userDetail->status === \App\Models\User::STATUS_ACTIVE;
        $statusLabel = $statusLabels[$userDetail->status] ?? ucfirst($userDetail->status);
    @endphp

    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">Manajemen User</div>
                <h2 class="page-title mt-1 mb-0">{{ $userDetail->name }}</h2>
                <div class="text-secondary mt-2">Detail lengkap akun pengguna, status operasional, dan jejak aktivitas.</div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Kembali ke daftar user
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card user-detail-hero-card">
                <div class="card-body">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-8">
                            <div class="d-flex flex-column flex-md-row gap-4 align-items-md-start">
                                <div class="user-detail-avatar-frame">
                                    @if ($userDetail->avatarUrl())
                                        <img
                                            src="{{ $userDetail->avatarUrl() }}"
                                            alt="Avatar {{ $userDetail->name }}"
                                            class="user-detail-avatar-image"
                                            onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');"
                                        >
                                        <div class="user-detail-avatar d-none">
                                            {{ strtoupper(substr($userDetail->name, 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="user-detail-avatar">
                                            {{ strtoupper(substr($userDetail->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-fill">
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @forelse ($userDetail->roles as $role)
                                            <span class="badge {{ $roleBadgeClasses[$role->name] ?? 'bg-azure-lt text-azure' }}">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="badge bg-secondary-lt text-secondary">Tanpa role</span>
                                        @endforelse

                                        <span class="badge {{ $statusBadgeClasses[$userDetail->status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $statusLabel }}
                                        </span>

                                        @if ($isVerified)
                                            <span class="badge bg-success-lt text-success">Email Terverifikasi</span>
                                        @else
                                            <span class="badge bg-warning-lt text-warning">Email Belum Terverifikasi</span>
                                        @endif
                                    </div>

                                    <h3 class="mb-1">{{ $userDetail->name }}</h3>
                                    <div class="text-secondary mb-3 user-detail-subtitle">
                                        <span>{{ '@'.$userDetail->username }}</span>
                                        <span class="user-detail-separator"></span>
                                        <span>{{ $userDetail->email }}</span>
                                    </div>

                                    <div class="user-detail-meta-grid">
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Role</div>
                                            <div class="fw-semibold mt-2">{{ $primaryRole ?: 'Tanpa role' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Status</div>
                                            <div class="fw-semibold mt-2">{{ $statusLabel }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Login Terakhir</div>
                                            <div class="fw-semibold mt-2">{{ $userDetail->last_login_at ? $userDetail->last_login_at->translatedFormat('d M Y H:i') : 'Belum pernah login' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Tenant</div>
                                            <div class="fw-semibold mt-2">{{ $userDetail->tenant?->name ?? 'Platform Internal' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Dibuat Oleh</div>
                                            <div class="fw-semibold mt-2">{{ $userDetail->creator?->name ?? 'System / Seeder' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Verifikasi</div>
                                            <div class="fw-semibold mt-2">{{ $isVerified ? 'Terverifikasi' : 'Belum verifikasi' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Terdaftar</div>
                                            <div class="fw-semibold mt-2">{{ $userDetail->created_at->translatedFormat('d M Y H:i') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="user-detail-actions">
                                <div class="user-detail-actions-head">
                                    <div class="text-secondary small text-uppercase fw-bold">Aksi Cepat</div>
                                    <h3 class="card-title mt-2 mb-1">Kontrol Akun</h3>
                                </div>

                                @if ($canManageRoles)
                                    <form method="POST" action="{{ route('admin.users.update-role', $userDetail) }}" class="user-detail-inline-form user-detail-action-block" onsubmit="return confirm('Yakin ingin mengubah role user ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <label for="detail_role" class="form-label mb-2">Role Aktif</label>
                                        <div class="d-flex gap-2">
                                            <select id="detail_role" name="role" class="form-select form-select-pretty">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}" @selected($userDetail->roles->contains('name', $role->name))>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-outline-primary">Simpan</button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-secondary mb-0 user-detail-action-block">
                                        Role aktif hanya dapat diubah oleh Superadmin.
                                    </div>
                                @endif

                                @if ($canManageTargetUser)
                                    <div class="user-detail-action-stack">
                                        <div class="user-detail-action-title">Tindakan sensitif</div>

                                        <form method="POST" action="{{ route('admin.users.update-password', $userDetail) }}" onsubmit="return confirm('Reset password user ini ke password default? User akan diwajibkan ganti password saat login berikutnya.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ti ti-key me-1"></i>
                                                Reset Password Default
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.update-status', $userDetail) }}" onsubmit="return confirm('Yakin ingin memperbarui status akun ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <input
                                                type="hidden"
                                                name="status"
                                                value="{{ $isActive ? \App\Models\User::STATUS_INACTIVE : \App\Models\User::STATUS_ACTIVE }}"
                                            >
                                            <button
                                                type="submit"
                                                class="btn {{ $isActive ? 'btn-outline-danger' : 'btn-outline-success' }} w-100"
                                                @disabled($currentUser?->id === $userDetail->id && $isActive)
                                            >
                                                <i class="ti {{ $isActive ? 'ti-user-off' : 'ti-user-check' }} me-1"></i>
                                                {{ $isActive ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                                            </button>
                                        </form>

                                        @unless ($isVerified)
                                            <form method="POST" action="{{ route('admin.users.resend-verification', $userDetail) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-primary w-100">
                                                    <i class="ti ti-mail-forward me-1"></i>
                                                    Kirim Ulang Verifikasi
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.users.verify-email', $userDetail) }}" onsubmit="return confirm('Tandai email user ini sebagai terverifikasi?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-success w-100">
                                                    <i class="ti ti-circle-check me-1"></i>
                                                    Verifikasi Manual
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0 user-detail-action-block">
                                        Aksi sensitif untuk akun Superadmin hanya tersedia bagi Superadmin.
                                    </div>
                                @endif

                                @if ($canDeleteUser)
                                    <form method="POST" action="{{ route('admin.users.destroy', $userDetail) }}" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger w-100">
                                            <i class="ti ti-trash me-1"></i>
                                            Hapus User
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Profil User</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="user-detail-info-list">
                        <div class="user-detail-info-row">
                            <span>Nama lengkap</span>
                            <strong class="user-detail-info-value">{{ $userDetail->name }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Username</span>
                            <strong class="user-detail-info-value">{{ '@'.$userDetail->username }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Email</span>
                            <strong class="user-detail-info-value">{{ $userDetail->email }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>No. HP</span>
                            <strong class="user-detail-info-value">{{ $userDetail->phone_number ?: 'Belum diisi' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Role aktif</span>
                            <strong class="user-detail-info-value">{{ $userDetail->getRoleNames()->implode(', ') ?: 'Tanpa role' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Asal pondok / tenant</span>
                            <strong class="user-detail-info-value">{{ $userDetail->tenant?->name ?? 'Platform Internal' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Status akun</span>
                            <strong class="user-detail-info-value">{{ $statusLabel }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Email verification</span>
                            <strong class="user-detail-info-value">{{ $isVerified ? 'Terverifikasi' : 'Belum terverifikasi' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Terakhir login</span>
                            <strong class="user-detail-info-value">{{ $userDetail->last_login_at ? $userDetail->last_login_at->translatedFormat('d M Y H:i') : 'Belum pernah login' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Password change required</span>
                            <strong class="user-detail-info-value">{{ $userDetail->password_change_required ? 'Ya' : 'Tidak' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Avatar</span>
                            <strong class="user-detail-info-value">{{ $userDetail->avatar_path ? 'Sudah diupload' : 'Belum ada avatar' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Dibuat oleh</span>
                            <strong class="user-detail-info-value">{{ $userDetail->creator?->name ?? 'System / Seeder' }}</strong>
                        </div>
                        <div class="user-detail-info-row">
                            <span>Terdaftar</span>
                            <strong class="user-detail-info-value">{{ $userDetail->created_at->translatedFormat('d M Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Riwayat Perubahan Role</h3>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($roleHistory as $log)
                        @php
                            $fromRole = data_get($log->properties, 'from');
                            $toRole = data_get($log->properties, 'to') ?? data_get($log->properties, 'role');
                        @endphp
                        <div class="user-detail-timeline-item">
                            <div class="user-detail-timeline-icon bg-purple-lt text-purple">
                                <i class="ti ti-user-shield"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2">
                                    <div class="fw-semibold">{{ str($log->action)->headline() }}</div>
                                    <div class="text-secondary small">{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</div>
                                </div>
                                <div class="text-secondary small mt-1">
                                    Pelaku: {{ $log->actor_name ?? 'System' }}
                                </div>
                                <div class="mt-2">
                                    @if ($log->action === 'user_created')
                                        <span class="badge bg-azure-lt text-azure">Role awal: {{ $toRole ?? 'Tanpa role' }}</span>
                                    @else
                                        <span class="badge bg-secondary-lt text-secondary">{{ $fromRole ?: 'Tanpa role' }}</span>
                                        <i class="ti ti-arrow-right mx-1 text-secondary"></i>
                                        <span class="badge bg-purple-lt text-purple">{{ $toRole ?? 'Tanpa role' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada riwayat perubahan role untuk user ini.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Riwayat Aktivitas</h3>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Aksi</th>
                                <th>Pelaku</th>
                                <th>Target</th>
                                <th>Detail</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activityLogs as $log)
                                <tr>
                                    <td class="text-secondary small">{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-azure-lt text-azure">{{ str($log->action)->headline() }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $log->actor_name ?? 'System' }}</div>
                                        <div class="text-secondary small">{{ $log->actor?->username ? '@'.$log->actor->username : 'Tanpa akun login' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $log->target_name ?? '-' }}</div>
                                        <div class="text-secondary small">{{ $log->target_type ? class_basename($log->target_type) : 'Tanpa target' }}</div>
                                    </td>
                                    <td class="text-secondary small">{{ $log->description ?? '-' }}</td>
                                    <td class="text-secondary small">{{ $log->ip_address ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada aktivitas yang tercatat untuk user ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
