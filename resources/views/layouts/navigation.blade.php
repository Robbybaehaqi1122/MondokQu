@php
    $user = Auth::user();
    $roles = $user?->getRoleNames() ?? collect();
    $roleLabel = $roles->implode(', ') ?: 'Tanpa role';
@endphp

<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" id="mobile-sidebar-toggle" aria-controls="sidebar-menu" aria-expanded="true" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <h1 class="navbar-brand navbar-brand-autodark w-100">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-card text-decoration-none text-reset">
                <span class="sidebar-brand-mark">
                    <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="Logo Mondok Qu" class="sidebar-brand-image">
                </span>
                <span class="sidebar-brand-copy">
                    <span class="sidebar-brand-title">Mondok Qu</span>
                </span>
            </a>
        </h1>

        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
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

        <div class="sidebar-menu is-open" id="sidebar-menu">
            <div class="sidebar-menu-inner pt-lg-3">
                <a class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('dashboard.home') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="sidebar-link-icon">
                        <i class="ti ti-home"></i>
                    </span>
                    <span>Dashboard</span>
                </a>

                @if ($user->hasRole('Admin'))
                    <div class="sidebar-section-title">Modul</div>
                    <details class="sidebar-dropdown" @if (request()->routeIs('admin.users')) open @endif>
                        <summary class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                            <span class="sidebar-link-icon">
                                <i class="ti ti-shield-lock"></i>
                            </span>
                            <span class="flex-grow-1">Autentikasi</span>
                            <span class="sidebar-dropdown-arrow">
                                <i class="ti ti-chevron-down"></i>
                            </span>
                        </summary>

                        <div class="sidebar-submenu">
                            <a class="sidebar-sublink {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                <span class="sidebar-link-icon">
                                    <i class="ti ti-users"></i>
                                </span>
                                <span>Manajemen User</span>
                            </a>
                        </div>
                    </details>
                @endif

                @if ($user->hasRole('Pengurus'))
                    <div class="sidebar-section-title">Modul Pengurus</div>
                    <a class="sidebar-link {{ request()->routeIs('pengurus.santri') ? 'active' : '' }}" href="{{ route('pengurus.santri') }}">
                        <span class="sidebar-link-icon">
                            <i class="ti ti-school"></i>
                        </span>
                        <span>Data Santri</span>
                    </a>
                @endif

                @if ($user->hasRole('Bendahara'))
                    <div class="sidebar-section-title">Modul Bendahara</div>
                    <a class="sidebar-link {{ request()->routeIs('bendahara.laporan') ? 'active' : '' }}" href="{{ route('bendahara.laporan') }}">
                        <span class="sidebar-link-icon">
                            <i class="ti ti-report-money"></i>
                        </span>
                        <span>Laporan Keuangan</span>
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
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
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
