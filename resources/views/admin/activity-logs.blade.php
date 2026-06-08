<x-app-layout>
    @php
        $currentUser = auth()->user();
        $canManageActivityLogs = $currentUser?->hasRole('Superadmin') ?? false;
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Log Activity</h2>
            <div class="text-secondary mt-1">
                @if ($currentUser?->isSuperAdmin())
                    Catatan aktivitas penting lintas platform untuk audit trail autentikasi dan administrasi sistem.
                @else
                    Catatan aktivitas internal tenant untuk melihat perubahan data santri, user, tagihan, dan pembayaran.
                @endif
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Log</div>
                            <div class="h1 mb-1">{{ $logSummary['total'] }}</div>
                            <div class="text-secondary small">Dalam cakupan akses Anda.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Hasil Filter</div>
                            <div class="h1 mb-1">{{ $logSummary['filtered'] }}</div>
                            <div class="text-secondary small">Sesuai pencarian aktif.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Hari Ini</div>
                            <div class="h1 mb-1">{{ $logSummary['today'] }}</div>
                            <div class="text-secondary small">Aktivitas tercatat hari ini.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Aksi Hapus</div>
                            <div class="h1 mb-1">{{ $logSummary['destructive'] }}</div>
                            <div class="text-secondary small">Jejak aktivitas penghapusan.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Riwayat Aktivitas</h3>
                            <p class="text-secondary mb-0">Menampilkan siapa melakukan apa, targetnya siapa, kapan terjadi.</p>
                        </div>

                        <div class="dropdown">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical me-1"></i>
                                Aksi
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a
                                    href="{{ route('admin.activity-logs.export', request()->only(['search', 'action', 'actor_id', 'date_from', 'date_to'])) }}"
                                    class="dropdown-item"
                                >
                                    <i class="ti ti-download me-2"></i>
                                    Export CSV
                                </a>

                                @if ($canManageActivityLogs)
                                    <form method="POST" action="{{ route('admin.activity-logs.destroy-all') }}" onsubmit="return confirm('Yakin ingin menghapus semua log activity? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ti ti-trash me-2"></i>
                                            Hapus Semua Log
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.activity-logs') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="activity-search" class="form-label">Cari</label>
                            <input id="activity-search" type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Pelaku, aksi, target, detail, IP">
                        </div>
                        <div class="col-md-3">
                            <label for="activity-action" class="form-label">Aksi</label>
                            <select id="activity-action" name="action" class="form-select">
                                <option value="">Semua aksi</option>
                                @foreach ($actionOptions as $action)
                                    <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ str($action)->headline() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="activity-actor" class="form-label">Pelaku</label>
                            <select id="activity-actor" name="actor_id" class="form-select">
                                <option value="">Semua pelaku</option>
                                @foreach ($actors as $actor)
                                    <option value="{{ $actor->id }}" @selected((int) $filters['actor_id'] === $actor->id)>{{ $actor->name }} (@{{ $actor->username }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="activity-date-from" class="form-label">Dari</label>
                            <input id="activity-date-from" type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="activity-date-to" class="form-label">Sampai</label>
                            <input id="activity-date-to" type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>
                                Terapkan Filter
                            </button>
                            <a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pelaku</th>
                                <th>Aksi</th>
                                <th>Target</th>
                                <th>Detail</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="text-secondary small">{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $log->actor_name ?? 'System' }}</div>
                                        <div class="text-secondary small">{{ $log->actor?->username ? '@'.$log->actor->username : 'Tanpa akun login' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-azure-lt text-azure">{{ str($log->action)->headline() }}</span>
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
                                    <td colspan="6" class="text-secondary">Belum ada aktivitas yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="card-footer">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
