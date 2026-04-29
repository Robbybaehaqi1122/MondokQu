<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">Admin Panel</div>
            <h2 class="page-title mt-1">Dashboard Operasional Pondok</h2>
            <div class="text-secondary mt-2">Pantau kondisi tenant, santri, user, dan ritme operasional harian dari satu halaman.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        @if ($tenantLifecycleNotice)
            <div class="col-12">
                <div class="alert alert-{{ $tenantLifecycleNotice['style'] }} mb-0" role="alert">
                    <div class="d-flex">
                        <div>
                            <h4 class="alert-title mb-1">{{ $tenantLifecycleNotice['title'] }}</h4>
                            <div>{{ $tenantLifecycleNotice['message'] }}</div>
                            <div class="mt-2 fw-semibold">{{ $tenantLifecycleNotice['action'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-4">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h3 class="card-title mb-0">{{ $tenantSummary['title'] }}</h3>
                                <span class="badge bg-{{ $tenantSummary['badge_color'] }}-lt text-{{ $tenantSummary['badge_color'] }}">{{ $tenantSummary['badge'] }}</span>
                            </div>
                            <p class="text-secondary mb-1 mt-2">{{ $tenantSummary['description'] }}</p>
                            <div class="text-secondary small">{{ $tenantSummary['meta'] }}</div>
                        </div>
                        <div class="text-lg-end">
                            <div class="text-secondary small text-uppercase fw-bold">Ringkasan Cepat</div>
                            <div class="h2 mb-1">{{ $santriStats['active_santri'] }} Santri Aktif</div>
                            <div class="text-secondary small">{{ $stats['active_users'] }} user aktif siap mengelola operasional pondok.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-azure-lt text-azure">
                                    <i class="ti ti-users fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Total User</div>
                                    <div class="h1 mb-1">{{ $stats['total_users'] }}</div>
                                    <div class="text-secondary small">Seluruh akun pengelola yang terdaftar.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-success-lt text-success">
                                    <i class="ti ti-user-check fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">User Aktif</div>
                                    <div class="h1 mb-1">{{ $stats['active_users'] }}</div>
                                    <div class="text-secondary small">Akun yang saat ini bisa login.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-blue-lt text-blue">
                                    <i class="ti ti-school fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Total Santri</div>
                                    <div class="h1 mb-1">{{ $santriStats['total_santri'] }}</div>
                                    <div class="text-secondary small">Seluruh data santri terdaftar.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-success-lt text-success">
                                    <i class="ti ti-user-heart fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">Santri Aktif</div>
                                    <div class="h1 mb-1">{{ $santriStats['active_santri'] }}</div>
                                    <div class="text-secondary small">Santri yang masih aktif mondok.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">User per Role</h3>
                        <div class="text-secondary small">Distribusi user berdasarkan role yang aktif di tenant atau platform Anda.</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-4">
                        @forelse ($roleDistribution as $role)
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <div class="fw-semibold">{{ $role['name'] }}</div>
                                        <div class="text-secondary small">{{ $role['count'] }} user</div>
                                    </div>
                                    <div class="fw-bold text-secondary">{{ $role['count'] }}</div>
                                </div>
                                <div class="progress progress-sm">
                                    <div
                                        class="progress-bar"
                                        style="width: {{ max(6, $role['percentage']) }}%"
                                        role="progressbar"
                                        aria-valuenow="{{ $role['count'] }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-secondary">Belum ada role yang bisa ditampilkan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Monitoring Cepat</h3>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Santri Baru Bulan Ini</div>
                            <div class="text-secondary small">Penambahan data santri sejak awal bulan.</div>
                        </div>
                        <span class="badge bg-blue-lt text-blue">{{ $newSantriThisMonth }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">User Belum Login</div>
                            <div class="text-secondary small">Akun dibuat tetapi belum pernah masuk ke sistem.</div>
                        </div>
                        <span class="badge bg-purple-lt text-purple">{{ $stats['never_logged_in_users'] }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Login Hari Ini</div>
                            <div class="text-secondary small">Aktivitas login sukses hari ini.</div>
                        </div>
                        <span class="badge bg-success-lt text-success">{{ $loginCountToday }}</span>
                    </div>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">User Baru Minggu Ini</div>
                            <div class="text-secondary small">Akun baru sejak awal minggu.</div>
                        </div>
                        <span class="badge bg-indigo-lt text-indigo">{{ $newUsersThisWeek }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Sebaran Santri per Kamar</h3>
                        <div class="text-secondary small">Lihat kamar yang paling padat untuk pengawasan hunian.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($roomDistribution as $room)
                        <div class="list-group-item d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">{{ $room['room_name'] }}</div>
                                <div class="text-secondary small">Santri terdaftar di kamar ini.</div>
                            </div>
                            <span class="badge bg-azure-lt text-azure">{{ $room['santri_count'] }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada data kamar yang bisa ditampilkan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Sebaran Santri per Angkatan</h3>
                        <div class="text-secondary small">Pantau komposisi angkatan untuk kebutuhan pembinaan dan laporan.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($entryYearDistribution as $year)
                        <div class="list-group-item d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold">Angkatan {{ $year['entry_year'] }}</div>
                                <div class="text-secondary small">Jumlah santri yang masuk pada tahun tersebut.</div>
                            </div>
                            <span class="badge bg-indigo-lt text-indigo">{{ $year['santri_count'] }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada data angkatan yang bisa ditampilkan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Login Terakhir User</h3>
                        <div class="text-secondary small">Membantu melihat siapa yang aktif memakai sistem belakangan ini.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentUsers as $user)
                        <div class="list-group-item d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="text-secondary small">
                                    {{ '@'.$user->username }}
                                    @if ($user->roles->isNotEmpty())
                                        · {{ $user->roles->pluck('name')->implode(', ') }}
                                    @endif
                                </div>
                            </div>
                            <div class="text-end text-secondary small">
                                {{ $user->last_login_at ? $user->last_login_at->translatedFormat('d M Y H:i') : 'Belum pernah login' }}
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada data user untuk ditampilkan.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">Santri Terbaru</h3>
                        <div class="text-secondary small">Data santri yang paling baru masuk ke sistem.</div>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($recentSantri as $santri)
                        <div class="list-group-item d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="fw-semibold">{{ $santri->full_name }}</div>
                                <div class="text-secondary small">NIS {{ $santri->nis }} · {{ $santri->room_name ?: 'Kamar belum diatur' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success-lt text-success">{{ $santri->statusLabel() }}</div>
                                <div class="text-secondary small mt-1">{{ $santri->created_at->translatedFormat('d M Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada data santri yang bisa ditampilkan.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
