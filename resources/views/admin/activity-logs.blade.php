<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Log Activity</h2>
            <div class="text-secondary mt-1">Catatan aktivitas penting untuk audit trail autentikasi dan administrasi sistem.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Riwayat Aktivitas</h3>
                            <p class="text-secondary mb-0">Menampilkan siapa melakukan apa, targetnya siapa, kapan terjadi.</p>
                        </div>

                        @if (auth()->user()?->hasAnyRole(['Superadmin', 'Admin']))
                            <form method="POST" action="{{ route('admin.activity-logs.destroy-all') }}" onsubmit="return confirm('Yakin ingin menghapus semua log activity? Tindakan ini tidak bisa dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="ti ti-trash me-1"></i>
                                    Hapus Semua Log
                                </button>
                            </form>
                        @endif
                    </div>
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
