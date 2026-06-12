<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Backup & Restore Database</h2>
                <div class="text-secondary mt-1">Kelola file backup database per tenant pondok.</div>
            </div>
            @if ($isSuperAdmin || $tenantOptions->isNotEmpty())
                <form method="POST" action="{{ route('backup.store') }}" class="d-flex gap-2 align-items-end">
                    @csrf
                    @if ($isSuperAdmin)
                        <div>
                            <label for="tenant" class="form-label visually-hidden">Pilih Tenant</label>
                            <select name="tenant" id="tenant" class="form-select" required>
                                <option value="">Pilih Tenant</option>
                                @foreach ($tenantOptions as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-cloud-upload me-1"></i>
                        Backup Sekarang
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('backup.store') }}" class="d-flex gap-2">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-cloud-upload me-1"></i>
                        Backup Sekarang
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <div class="d-flex align-items-center">
                <i class="ti ti-check-circle me-2"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <div class="d-flex align-items-center">
                <i class="ti ti-alert-circle me-2"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        @if ($isSuperAdmin)
                            <th>Tenant</th>
                        @endif
                        <th>Tipe</th>
                        <th>Ukuran</th>
                        <th>Tabel</th>
                        <th>Baris</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            @if ($isSuperAdmin)
                                <td>
                                    <div class="fw-semibold">{{ $backup->tenant?->name ?? '-' }}</div>
                                </td>
                            @endif
                            <td>
                                <span class="badge {{ $backup->type === 'scheduled' ? 'bg-secondary-lt' : 'bg-primary-lt' }}">
                                    {{ ucfirst($backup->type) }}
                                </span>
                            </td>
                            <td>{{ $backup->sizeForHumans() }}</td>
                            <td>{{ $backup->tables_count ?? '-' }}</td>
                            <td>{{ $backup->total_rows ? number_format($backup->total_rows) : '-' }}</td>
                            <td>
                                @php
                                    $statusBadge = match ($backup->status) {
                                        'completed' => 'bg-success-lt text-success',
                                        'processing' => 'bg-info-lt text-info',
                                        'pending' => 'bg-secondary-lt text-secondary',
                                        'failed' => 'bg-danger-lt text-danger',
                                        default => 'bg-secondary-lt text-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">
                                    @if ($backup->isProcessing())
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                    @endif
                                    {{ ucfirst($backup->status) }}
                                </span>
                                @if ($backup->isFailed() && $backup->error_message)
                                    <br>
                                    <small class="text-danger">{{ $backup->error_message }}</small>
                                @endif
                            </td>
                            <td>{{ $backup->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        Aksi
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @if ($backup->isCompleted())
                                            <a href="{{ route('backup.download', $backup) }}" class="dropdown-item">
                                                <i class="ti ti-download me-2"></i>
                                                Download
                                            </a>
                                            <a href="{{ route('backup.restore', $backup) }}" class="dropdown-item text-warning"
                                                onclick="return confirm('Yakin ingin merestore database dari backup ini? Data tenant saat ini akan diganti dengan data backup.')">
                                                <i class="ti ti-refresh me-2"></i>
                                                Restore
                                            </a>
                                            <div class="dropdown-divider"></div>
                                        @endif
                                        <form method="POST" action="{{ route('backup.destroy', $backup) }}" onsubmit="return confirm('Yakin ingin menghapus file backup ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="ti ti-trash me-2"></i>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSuperAdmin ? 8 : 7 }}" class="text-secondary text-center py-4">
                                <i class="ti ti-cloud-off fs-2 mb-2 d-block"></i>
                                Belum ada file backup. Klik "Backup Sekarang" untuk membuat backup pertama.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($backups->hasPages())
            <div class="card-footer">
                {{ $backups->links() }}
            </div>
        @endif
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Informasi Backup</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="ti ti-database fs-2 text-primary"></i>
                        </div>
                        <div>
                            <div class="text-secondary small">Metode Backup</div>
                            <div class="fw-semibold">Export SQL per Tenant</div>
                            <small class="text-secondary">Data diekspor sesuai tenant_id</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="ti ti-calendar-event fs-2 text-primary"></i>
                        </div>
                        <div>
                            <div class="text-secondary small">Backup Otomatis</div>
                            <div class="fw-semibold">Setiap Minggu</div>
                            <small class="text-secondary">Hari Minggu jam 02:00</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="ti ti-trash-x fs-2 text-danger"></i>
                        </div>
                        <div>
                            <div class="text-secondary small">Retensi Backup</div>
                            <div class="fw-semibold">{{ config('backups.retention_days', 30) }} Hari</div>
                            <small class="text-secondary">Backup lama otomatis dihapus</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
