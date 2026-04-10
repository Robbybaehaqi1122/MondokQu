<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">Admin Panel</div>
            <h2 class="page-title mt-1">System Monitoring Dashboard</h2>
            <div class="text-secondary mt-2">Pantau kondisi user dan aktivitas autentikasi secara ringkas dari satu halaman.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
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
                                    <div class="text-secondary small">Seluruh akun terdaftar.</div>
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
                                    <div class="text-secondary small">Bisa login ke sistem.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-secondary-lt text-secondary">
                                    <i class="ti ti-user-off fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">User Inactive</div>
                                    <div class="h1 mb-1">{{ $stats['inactive_users'] }}</div>
                                    <div class="text-secondary small">Perlu aktivasi ulang.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar bg-danger-lt text-danger">
                                    <i class="ti ti-alert-triangle fs-2"></i>
                                </span>
                                <div>
                                    <div class="text-secondary small text-uppercase fw-bold">User Suspended</div>
                                    <div class="h1 mb-1">{{ $stats['suspended_users'] }}</div>
                                    <div class="text-secondary small">Akses sedang dibatasi.</div>
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
                        <div class="text-secondary small">Distribusi user berdasarkan role yang aktif di sistem.</div>
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
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Fokus Operasional</div>
                            <div class="text-secondary small">Prioritaskan akun inactive, suspended, dan belum login.</div>
                        </div>
                        <span class="badge bg-azure-lt text-azure">Actionable</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
