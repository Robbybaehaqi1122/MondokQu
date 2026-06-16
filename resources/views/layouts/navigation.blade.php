@php
    $user = Auth::user();
    $roles = $user?->getRoleNames() ?? collect();
    $roleLabel = $roles->implode(', ') ?: 'Tanpa role';
    $userInitial = strtoupper(substr($user->name, 0, 1));
    $canOpenOperationalReports = $user->can('manage kamar') || $user->canAny(['create izin', 'approve izin']);
    $canOpenAbsensiModule = $user->can('manage absensi');
    $canOpenTahfidzModule = $user->can('manage tahfidz');
    $canOpenPelanggaranModule = $user->can('manage pelanggaran');
    $canOpenKomunikasiModule = $user->can('manage komunikasi');
    $canOpenAkademikModule = $user->can('manage akademik');
    $canOpenKesehatanModule = $user->can('manage kesehatan');
    $canOpenSantriModule = $user->can('view santri') || $canOpenOperationalReports || $user->can('view pembayaran') || $user->can('view laporan keuangan');
    $canOpenCoreModules = $canOpenAbsensiModule || $canOpenTahfidzModule || $canOpenPelanggaranModule || $canOpenKomunikasiModule || $canOpenAkademikModule || $canOpenKesehatanModule || $canOpenSantriModule;
    $unreadNotifications = collect();
    $unreadNotificationCount = 0;

    if ($user) {
        $unreadNotifications = $user->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get();
        $unreadNotificationCount = $user->unreadNotifications()->count();
    }
@endphp

<div class="mobile-topbar d-lg-none">
    <button class="mobile-topbar-toggle" type="button" id="mobile-sidebar-toggle" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Buka navigasi">
        <i class="ti ti-menu-2"></i>
    </button>

    <a href="{{ route('dashboard') }}" class="mobile-topbar-brand text-decoration-none text-reset">
        <span class="mobile-topbar-brand-mark">
            <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="Logo Mondok Qu" class="sidebar-brand-image" loading="lazy">
        </span>
        <span class="mobile-topbar-brand-copy">
            <span class="mobile-topbar-brand-title">Mondok Qu</span>
            <span class="mobile-topbar-brand-subtitle">{{ $roleLabel }}</span>
        </span>
    </a>

    <a href="{{ route('notifications.index') }}" class="mobile-topbar-notification" aria-label="Buka notifikasi">
        <i class="ti ti-bell"></i>
        @if ($unreadNotificationCount > 0)
            <span class="mobile-topbar-notification-badge">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
        @endif
    </a>

    <a href="{{ route('profile.edit') }}" class="mobile-topbar-profile" aria-label="Buka profil pengguna">
        @if ($user->avatarUrl())
            <span class="avatar avatar-sm" style="background-image: url('{{ $user->avatarUrl() }}')"></span>
        @else
            <span class="avatar avatar-sm">{{ $userInitial }}</span>
        @endif
    </a>
</div>

<div class="mobile-sidebar-backdrop d-lg-none" id="mobile-sidebar-backdrop" hidden></div>

<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark" data-bs-theme="dark">
    <div class="container-fluid">
        <div class="mobile-sidebar-head d-lg-none">
            <button class="mobile-sidebar-close" type="button" id="mobile-sidebar-close" aria-label="Tutup navigasi">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <h1 class="navbar-brand navbar-brand-autodark w-100 mobile-sidebar-brand-wrap">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-card text-decoration-none text-reset">
                <span class="sidebar-brand-mark">
                    <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="Logo Mondok Qu" class="sidebar-brand-image" loading="lazy">
                </span>
                <span class="sidebar-brand-copy">
                    <span class="sidebar-brand-title">Mondok Qu</span>
                </span>
            </a>
        </h1>

        <div class="mobile-sidebar-user d-lg-none">
            <a href="{{ route('profile.edit') }}" class="mobile-sidebar-user-card text-decoration-none">
                <span class="mobile-sidebar-user-avatar">
                    @if ($user->avatarUrl())
                        <span class="avatar avatar-md" style="background-image: url('{{ $user->avatarUrl() }}')"></span>
                    @else
                        <span class="avatar avatar-md">{{ $userInitial }}</span>
                    @endif
                </span>
                <span class="mobile-sidebar-user-copy">
                    <span class="mobile-sidebar-user-name">{{ $user->name }}</span>
                    <span class="mobile-sidebar-user-meta">{{ '@'.$user->username }}</span>
                    <span class="mobile-sidebar-user-meta">{{ $roleLabel }}</span>
                </span>
                <span class="mobile-sidebar-user-arrow">
                    <i class="ti ti-chevron-right"></i>
                </span>
            </a>

            <div class="mobile-sidebar-user-actions">
                <a href="{{ route('profile.edit') }}" class="mobile-sidebar-action">
                    <i class="ti ti-user"></i>
                    <span>Profil Saya</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mobile-sidebar-action mobile-sidebar-action-danger">
                        <i class="ti ti-logout"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="navbar-nav flex-row d-lg-none d-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    @if ($user->avatarUrl())
                        <span class="avatar avatar-sm" style="background-image: url('{{ $user->avatarUrl() }}')"></span>
                    @else
                        <span class="avatar avatar-sm">{{ $userInitial }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="sidebar-menu" id="sidebar-menu">
            <div class="sidebar-menu-inner pt-lg-3">
                <a class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('dashboard.home') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="sidebar-link-icon">
                        <i class="ti ti-home"></i>
                    </span>
                    <span>Dashboard</span>
                </a>

                @if ($canOpenCoreModules)
                    <div class="sidebar-section-title">Modul</div>
                @endif

                @if ($canOpenAbsensiModule)
                    <details class="sidebar-dropdown" @if (request()->routeIs('attendance.*')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-calendar-check"></i>
                            </span>
                            <span class="flex-grow-1">AbsenQu</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('attendance.dashboard') ? 'active' : '' }}" href="{{ route('attendance.dashboard') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-dashboard"></i>
                                </span>
                                <span>Dashboard Absensi</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('attendance.activities.*') ? 'active' : '' }}" href="{{ route('attendance.activities.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-list-check"></i>
                                </span>
                                <span>Master Kegiatan Absensi</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('attendance.sessions.*') ? 'active' : '' }}" href="{{ route('attendance.sessions.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-calendar-time"></i>
                                </span>
                                <span>Sesi Absensi Harian</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('attendance.reports.*') ? 'active' : '' }}" href="{{ route('attendance.reports.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-report-analytics"></i>
                                </span>
                                <span>Laporan Absensi</span>
                            </a>
                        </div>
                    </details>
                @endif

                @if ($canOpenTahfidzModule)
                    <details class="sidebar-dropdown" @if (request()->routeIs('tahfidz.*')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('tahfidz.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-book-2"></i>
                            </span>
                            <span class="flex-grow-1">TahfidzQu</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('tahfidz.dashboard') ? 'active' : '' }}" href="{{ route('tahfidz.dashboard') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-dashboard"></i>
                                </span>
                                <span>Dashboard Tahfidz</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('tahfidz.setoran.*') ? 'active' : '' }}" href="{{ route('tahfidz.setoran.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-clipboard-list"></i>
                                </span>
                                <span>Setoran Hafalan</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('tahfidz.jadwal.*') ? 'active' : '' }}" href="{{ route('tahfidz.jadwal.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-calendar-time"></i>
                                </span>
                                <span>Jadwal Setoran</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('tahfidz.rapor.*') ? 'active' : '' }}" href="{{ route('tahfidz.rapor.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-report-analytics"></i>
                                </span>
                                <span>Rapor Hafalan</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('tahfidz.targets.*') ? 'active' : '' }}" href="{{ route('tahfidz.targets.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-bullseye"></i>
                                </span>
                                <span>Target Hafalan</span>
                            </a>
                        </div>
                    </details>
                @endif

                @if ($canOpenAkademikModule)
                    <details class="sidebar-dropdown" @if (request()->routeIs('akademik.*')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('akademik.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-books"></i>
                            </span>
                            <span class="flex-grow-1">AkademikQu</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('akademik.dashboard') ? 'active' : '' }}" href="{{ route('akademik.dashboard') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-dashboard"></i></span>
                                <span>Dashboard</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('akademik.mata-pelajaran.*') ? 'active' : '' }}" href="{{ route('akademik.mata-pelajaran.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-list-details"></i></span>
                                <span>Mata Pelajaran</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('akademik.nilai.*') ? 'active' : '' }}" href="{{ route('akademik.nilai.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-edit-circle"></i></span>
                                <span>Nilai Santri</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('akademik.attitude.*') ? 'active' : '' }}" href="{{ route('akademik.attitude.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-heart-handshake"></i></span>
                                <span>Nilai Sikap</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('akademik.rapor.*') ? 'active' : '' }}" href="{{ route('akademik.rapor.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-report-analytics"></i></span>
                                <span>Rapor Digital</span>
                            </a>
                        </div>
                    </details>
                @endif

                @if ($canOpenPelanggaranModule)
                    <details class="sidebar-dropdown" @if (request()->routeIs('pelanggaran.*')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('pelanggaran.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-alert-triangle"></i>
                            </span>
                            <span class="flex-grow-1">PelanggaranQu</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('pelanggaran.dashboard') ? 'active' : '' }}" href="{{ route('pelanggaran.dashboard') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-dashboard"></i></span>
                                <span>Dashboard</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('pelanggaran.index') || request()->routeIs('pelanggaran.create') ? 'active' : '' }}" href="{{ route('pelanggaran.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-list-details"></i></span>
                                <span>Catatan Pelanggaran</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('pelanggaran.kategori.*') ? 'active' : '' }}" href="{{ route('pelanggaran.kategori.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-list-details"></i></span>
                                <span>Kategori & Poin</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('pelanggaran.sanction-thresholds.*') ? 'active' : '' }}" href="{{ route('pelanggaran.sanction-thresholds.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-gavel"></i></span>
                                <span>Tingkat Sanksi</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('pelanggaran.laporan.*') ? 'active' : '' }}" href="{{ route('pelanggaran.laporan.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-report-analytics"></i></span>
                                <span>Laporan</span>
                            </a>
                        </div>
                    </details>
                @endif

                @if ($canOpenKomunikasiModule)
                    <a class="sidebar-link {{ request()->routeIs('komunikasi.*') ? 'active' : '' }}" href="{{ route('komunikasi.index') }}">
                        <span class="sidebar-link-icon">
                            <i class="ti ti-message"></i>
                        </span>
                        <span>KomunikasiQu</span>
                    </a>
                @endif

                @if ($canOpenKesehatanModule)
                    <details class="sidebar-dropdown" @if (request()->routeIs('kesehatan.*')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('kesehatan.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-heartbeat"></i>
                            </span>
                            <span class="flex-grow-1">KesehatanQu</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('kesehatan.dashboard') ? 'active' : '' }}" href="{{ route('kesehatan.dashboard') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-dashboard"></i></span>
                                <span>Dashboard</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('kesehatan.rekam-medis.*') ? 'active' : '' }}" href="{{ route('kesehatan.rekam-medis.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-clipboard-heart"></i></span>
                                <span>Rekam Medis</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('kesehatan.pemeriksaan.*') ? 'active' : '' }}" href="{{ route('kesehatan.pemeriksaan.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-first-aid-kit"></i></span>
                                <span>Kunjungan UKS</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('kesehatan.obat.*') ? 'active' : '' }}" href="{{ route('kesehatan.obat.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-pill"></i></span>
                                <span>Stok Obat</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('kesehatan.laporan.*') ? 'active' : '' }}" href="{{ route('kesehatan.laporan.index') }}">
                                <span class="sidebar-link-icon"><i class="ti ti-report-analytics"></i></span>
                                <span>Laporan</span>
                            </a>
                        </div>
                    </details>
                @endif

                @if ($canOpenSantriModule)
                    <details class="sidebar-dropdown" @if (request()->routeIs('santri.index') || request()->routeIs('santri.show') || request()->routeIs('pengurus.santri') || request()->routeIs('rooms.*') || request()->routeIs('pengurus.izin.*') || request()->routeIs('pengurus.reports.*') || request()->routeIs('santri.payments.*') || request()->routeIs('santri.import.*')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('santri.index') || request()->routeIs('santri.show') || request()->routeIs('pengurus.santri') || request()->routeIs('rooms.*') || request()->routeIs('pengurus.izin.*') || request()->routeIs('pengurus.reports.*') || request()->routeIs('santri.payments.*') || request()->routeIs('santri.import.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-school"></i>
                            </span>
                            <span class="flex-grow-1">SantriQu</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            @if ($user->can('view santri'))
                                <a class="sidebar-sublink {{ request()->routeIs('santri.index') || request()->routeIs('santri.show') || request()->routeIs('pengurus.santri') ? 'active' : '' }}" href="{{ route('santri.index') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-users"></i>
                                    </span>
                                    <span>Manajemen Santri</span>
                                </a>
                            @endif

                            @if ($user->can('import santri'))
                                <a class="sidebar-sublink {{ request()->routeIs('santri.import.*') ? 'active' : '' }}" href="{{ route('santri.import.index') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-upload"></i>
                                    </span>
                                    <span>Import Santri</span>
                                </a>
                            @endif

                            @if ($user->can('manage kamar'))
                                <a class="sidebar-sublink {{ request()->routeIs('rooms.*') ? 'active' : '' }}" href="{{ route('rooms.index') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-bed"></i>
                                    </span>
                                    <span>Manajemen Kamar</span>
                                </a>
                            @endif

                            @if ($user->canAny(['create izin', 'approve izin']))
                                <a class="sidebar-sublink {{ request()->routeIs('pengurus.izin.*') ? 'active' : '' }}" href="{{ route('pengurus.izin.index') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-clipboard-check"></i>
                                    </span>
                                    <span>Perizinan Santri</span>
                                </a>
                            @endif

                            @if ($canOpenOperationalReports)
                                <a class="sidebar-sublink {{ request()->routeIs('pengurus.reports.*') ? 'active' : '' }}" href="{{ route('pengurus.reports.index') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-report-analytics"></i>
                                    </span>
                                    <span>Laporan Kamar & Izin</span>
                                </a>
                            @endif

                            @if ($user->can('view pembayaran'))
                                <a class="sidebar-sublink {{ request()->routeIs('santri.payments.*') ? 'active' : '' }}" href="{{ route('santri.payments.index') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-wallet"></i>
                                    </span>
                                    <span>Pembayaran Santri</span>
                                </a>
                            @endif

                            @if ($user->can('view laporan keuangan'))
                                <a class="sidebar-sublink {{ request()->routeIs('santri.payments.reports') ? 'active' : '' }}" href="{{ route('santri.payments.reports') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-report-money"></i>
                                    </span>
                                    <span>Laporan Bendahara</span>
                                </a>
                            @endif
                        </div>
                    </details>
                @endif

                @if ($user->hasAnyRole(['Superadmin', 'Admin']) || $user->canAny(['assign roles', 'manage system settings', 'view activity logs']))
                    @unless ($canOpenCoreModules)
                        <div class="sidebar-section-title">Modul</div>
                    @endunless
                    <details class="sidebar-dropdown" @if (request()->routeIs('admin.users') || request()->routeIs('admin.roles') || request()->routeIs('admin.permissions') || request()->routeIs('admin.activity-logs') || request()->routeIs('admin.audit-logs')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('admin.users') || request()->routeIs('admin.roles') || request()->routeIs('admin.permissions') || request()->routeIs('admin.activity-logs') || request()->routeIs('admin.audit-logs') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-shield-lock"></i>
                            </span>
                            <span class="flex-grow-1">Autentikasi</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            @if ($user->hasAnyRole(['Superadmin', 'Admin']))
                                <a class="sidebar-sublink {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-users"></i>
                                    </span>
                                    <span>Manajemen User</span>
                                </a>
                            @endif

                            @can('assign roles')
                                <a class="sidebar-sublink {{ request()->routeIs('admin.roles') ? 'active' : '' }}" href="{{ route('admin.roles') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-user-shield"></i>
                                    </span>
                                    <span>Manajemen Role</span>
                                </a>
                            @endcan

                            @can('manage system settings')
                                <a class="sidebar-sublink {{ request()->routeIs('admin.permissions') ? 'active' : '' }}" href="{{ route('admin.permissions') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-key"></i>
                                    </span>
                                    <span>Permission Management</span>
                                </a>
                            @endcan

                            @can('manage branding')
                                <a class="sidebar-sublink {{ request()->routeIs('branding.*') ? 'active' : '' }}" href="{{ route('branding.edit') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-palette"></i>
                                    </span>
                                    <span>Branding Pondok</span>
                                </a>
                            @endcan

                            @if ($user->hasRole('Superadmin') || $user->can('view activity logs'))
                                <a class="sidebar-sublink {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}" href="{{ route('admin.activity-logs') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-history"></i>
                                    </span>
                                    <span>Log Activity</span>
                                </a>
                            @endif

                            @if ($user->hasRole('Superadmin') || $user->can('view activity logs'))
                                <a class="sidebar-sublink {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}">
                                    <span class="sidebar-link-icon">
                                        <i class="ti ti-clipboard-list"></i>
                                    </span>
                                    <span>Audit Trail</span>
                                </a>
                            @endif
                        </div>
                    </details>
                @endif

                @if ($user->hasRole('Superadmin'))
                    <details class="sidebar-dropdown" @if (request()->routeIs('saas.dashboard') || request()->routeIs('saas.tenants.*') || request()->routeIs('saas.subscription-histories.*') || request()->routeIs('saas.billing-notes.*') || request()->routeIs('saas.resource-report') || request()->routeIs('backup.*')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('saas.dashboard') || request()->routeIs('saas.tenants.*') || request()->routeIs('saas.subscription-histories.*') || request()->routeIs('saas.billing-notes.*') || request()->routeIs('saas.resource-report') || request()->routeIs('backup.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-building-bank"></i>
                            </span>
                            <span class="flex-grow-1">SaaS</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('saas.dashboard') ? 'active' : '' }}" href="{{ route('saas.dashboard') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-chart-dots-3"></i>
                                </span>
                                <span>Dashboard SaaS</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('saas.tenants.*') ? 'active' : '' }}" href="{{ route('saas.tenants.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-building-community"></i>
                                </span>
                                <span>Tenant Management</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('saas.resource-report') ? 'active' : '' }}" href="{{ route('saas.resource-report') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-report-analytics"></i>
                                </span>
                                <span>Resource Report</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('saas.subscription-histories.*') ? 'active' : '' }}" href="{{ route('saas.subscription-histories.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-history-toggle"></i>
                                </span>
                                <span>Riwayat Subscription</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('saas.billing-notes.*') ? 'active' : '' }}" href="{{ route('saas.billing-notes.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-receipt-2"></i>
                                </span>
                                <span>Billing Notes</span>
                            </a>
                            <a class="sidebar-sublink {{ request()->routeIs('backup.*') ? 'active' : '' }}" href="{{ route('backup.index') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-cloud-upload"></i>
                                </span>
                                <span>Backup Database</span>
                            </a>
                        </div>
                    </details>
                @endif

                @if ($user->hasRole('Bendahara') && ! $canOpenSantriModule)
                    <div class="sidebar-section-title">Modul Bendahara</div>
                    <a class="sidebar-link {{ request()->routeIs('bendahara.laporan') ? 'active' : '' }}" href="{{ route('bendahara.laporan') }}">
                        <span class="sidebar-link-icon">
                            <i class="ti ti-report-money"></i>
                        </span>
                        <span>Laporan Keuangan</span>
                    </a>
                @endif

                @if ($user->hasRole('Musyrif/Ustadz') && ! ($canOpenTahfidzModule || $canOpenAbsensiModule || $canOpenPelanggaranModule || $canOpenKomunikasiModule))
                    <div class="sidebar-section-title">Modul Musyrif</div>
                    <a class="sidebar-link {{ request()->routeIs('tahfidz.*') ? 'active' : '' }}" href="{{ route('tahfidz.dashboard') }}">
                        <span class="sidebar-link-icon"><i class="ti ti-book-2"></i></span>
                        <span>Tahfidz</span>
                    </a>
                    <a class="sidebar-link {{ request()->routeIs('pelanggaran.*') ? 'active' : '' }}" href="{{ route('pelanggaran.dashboard') }}">
                        <span class="sidebar-link-icon"><i class="ti ti-alert-triangle"></i></span>
                        <span>Pelanggaran</span>
                    </a>
                    <a class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}" href="{{ route('attendance.dashboard') }}">
                        <span class="sidebar-link-icon"><i class="ti ti-calendar-check"></i></span>
                        <span>Absensi</span>
                    </a>
                @endif

                @if ($user->hasRole('Wali Santri'))
                    <div class="sidebar-section-title">Portal Wali</div>
                    <a class="sidebar-link {{ request()->routeIs('wali-santri.dashboard') ? 'active' : '' }}" href="{{ route('wali-santri.dashboard') }}">
                        <span class="sidebar-link-icon">
                            <i class="ti ti-users-group"></i>
                        </span>
                        <span>Portal Orang Tua</span>
                    </a>
                    <a class="sidebar-link {{ request()->routeIs('wali-santri.komunikasi.*') ? 'active' : '' }}" href="{{ route('wali-santri.komunikasi.index') }}">
                        <span class="sidebar-link-icon">
                            <i class="ti ti-message"></i>
                        </span>
                        <span>Komunikasi</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</aside>

<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
    <div class="container-xl">
        <div class="me-3">
            <button type="button" class="btn btn-outline-secondary btn-icon" id="sidebar-toggle" aria-label="Toggle sidebar">
                <i class="ti ti-layout-sidebar-left-collapse"></i>
            </button>
        </div>

        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item me-3">
                <button type="button" class="btn btn-outline-secondary btn-icon" id="theme-toggle" aria-label="Toggle dark mode">
                    <i class="ti ti-moon"></i>
                </button>
            </div>

            <div class="nav-item dropdown me-3">
                <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" aria-label="Buka notifikasi">
                    <span class="position-relative d-inline-flex align-items-center">
                        <i class="ti ti-bell fs-2"></i>
                        @if ($unreadNotificationCount > 0)
                            <span class="badge bg-red text-red-fg badge-notification badge-pill">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        @endif
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notification-dropdown">
                    <div class="dropdown-header d-flex align-items-center justify-content-between gap-3">
                        <span>Notifikasi</span>
                        <a href="{{ route('notifications.index') }}" class="small text-decoration-none">Lihat semua</a>
                    </div>
                    <div class="dropdown-divider m-0"></div>

                    @forelse ($unreadNotifications as $notification)
                        @php
                            $notificationTitle = data_get($notification->data, 'title', 'Notifikasi');
                            $notificationMessage = data_get($notification->data, 'message', 'Ada informasi baru untuk akun Anda.');
                            $notificationIcon = data_get($notification->data, 'icon', 'ti-bell');
                        @endphp
                        <a href="{{ route('notifications.show', $notification) }}" class="dropdown-item notification-dropdown-item">
                            <span class="avatar avatar-sm bg-primary-lt text-primary">
                                <i class="ti {{ $notificationIcon }}"></i>
                            </span>
                            <span class="min-width-0">
                                <span class="d-block fw-semibold text-truncate">{{ $notificationTitle }}</span>
                                <span class="d-block small text-secondary text-truncate">{{ $notificationMessage }}</span>
                            </span>
                        </a>
                    @empty
                        <div class="dropdown-item-text text-secondary">
                            Belum ada notifikasi baru.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    @if ($user->avatarUrl())
                        <span class="avatar avatar-sm" style="background-image: url('{{ $user->avatarUrl() }}')"></span>
                    @else
                        <span class="avatar avatar-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ $user->name }}</div>
                        <div class="mt-1 small text-secondary">{{ $roleLabel }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <div class="dropdown-header text-secondary">Signed in as</div>
                    <div class="dropdown-item-text">
                        <div class="fw-bold">{{ '@'.$user->username }}</div>
                        <div class="small text-secondary">{{ $user->email }}</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="ti ti-user me-2"></i>Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="ti ti-logout me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="navbar-menu">
            <div>
                <h2 class="page-title mb-0">{{ $roleLabel }}</h2>
                <div class="text-secondary">Panel operasional aplikasi pondok</div>
            </div>
        </div>
    </div>
</header>
