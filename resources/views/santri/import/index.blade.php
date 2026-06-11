<x-app-layout>
    @php
        $statusClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'processing' => 'bg-azure-lt text-azure',
            'completed' => 'bg-success-lt text-success',
            'failed' => 'bg-danger-lt text-danger',
        ];
        $statusLabels = [
            'pending' => 'Menunggu',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'failed' => 'Gagal',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Import Santri</h2>
            <div class="text-secondary mt-1">Import data santri secara massal dari file CSV atau Excel.</div>
        </div>
    </x-slot>

    @if (session('importResult'))
        @php $importResult = session('importResult'); @endphp
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Hasil Import</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-success-lt rounded">
                            <div class="h1 mb-1 text-success">{{ number_format($importResult['success']) }}</div>
                            <div class="text-secondary">Berhasil</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-danger-lt rounded">
                            <div class="h1 mb-1 text-danger">{{ number_format($importResult['failed']) }}</div>
                            <div class="text-secondary">Gagal</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-secondary-lt rounded">
                            <div class="h1 mb-1">{{ number_format($importResult['total']) }}</div>
                            <div class="text-secondary">Total Baris</div>
                        </div>
                    </div>
                </div>

                @if ($importResult['failed'] > 0 && filled($importResult['errors']))
                    <div class="mt-3">
                        <h4 class="text-danger">Detail Error</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter">
                                <thead>
                                    <tr>
                                        <th class="w-1">Baris</th>
                                        <th>NIS</th>
                                        <th>Nama</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($importResult['errors'] as $error)
                                        <tr>
                                            <td>{{ $error['row'] }}</td>
                                            <td>{{ $error['data']['nis'] ?? '-' }}</td>
                                            <td>{{ $error['data']['full_name'] ?? '-' }}</td>
                                            <td>
                                                <ul class="mb-0 ps-3">
                                                    @foreach ($error['errors'] as $message)
                                                        <li class="text-danger">{{ $message }}</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <a href="{{ route('santri.index') }}" class="btn btn-primary">
                    <i class="ti ti-users me-1"></i>
                    Lihat Data Santri
                </a>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row row-cards">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Upload File</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('santri.import.preview') }}" enctype="multipart/form-data">
                        @csrf

                        @if ($tenants->isNotEmpty())
                            <div class="mb-3">
                                <label for="tenant_id" class="form-label">Pilih Pondok / Tenant</label>
                                <select id="tenant_id" name="tenant_id" class="form-select" required>
                                    <option value="">-- Pilih Tenant --</option>
                                    @foreach ($tenants as $tenant)
                                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint mt-1">Pilih tenant tujuan import data santri.</div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File CSV atau Excel</label>
                            <input
                                id="file"
                                name="file"
                                type="file"
                                class="form-control @error('file') is-invalid @enderror"
                                accept=".csv,.xlsx,.xls"
                                required
                            >
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-hint mt-2">
                                Format: CSV (.csv), Excel (.xlsx, .xls). Maksimal 10 MB.
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="dropdown">
                                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-download me-1"></i>
                                    Download Template
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="{{ route('santri.import.template', ['format' => 'csv']) }}" class="dropdown-item">
                                        <i class="ti ti-file-text me-2"></i> CSV
                                    </a>
                                    <a href="{{ route('santri.import.template', ['format' => 'xlsx']) }}" class="dropdown-item">
                                        <i class="ti ti-file-spreadsheet me-2"></i> Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100" id="preview-btn">
                            <span id="preview-btn-icon"><i class="ti ti-eye me-1"></i></span>
                            <span class="spinner-border spinner-border-sm me-1" id="preview-spinner" role="status" hidden></span>
                            <span id="preview-btn-text">Preview Data</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Petunjuk Import</h3>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Download template CSV terlebih dahulu.</li>
                        <li class="mb-2">Isi template dengan data santri. Jangan mengubah header kolom.</li>
                        <li class="mb-2">Upload file yang sudah diisi untuk melihat preview data.</li>
                        <li>Konfirmasi import jika data sudah benar.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Riwayat Import</h3>
                        <div class="text-secondary small mt-2">Import yang diproses (langsung maupun background queue).</div>
                    </div>
                </div>

                @if ($dataImports->isEmpty())
                    <div class="card-body">
                        <div class="text-secondary">Belum ada riwayat import.</div>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($dataImports as $import)
                            <div class="list-group-item">
                                <div class="d-flex flex-column flex-lg-row justify-content-lg-between gap-3">
                                    <div class="flex-fill">
                                        <div class="fw-semibold">{{ $import->name }}</div>
                                        <div class="text-secondary small mt-1">
                                            {{ number_format($import->total_rows) }} baris
                                            @if ($import->completed_at)
                                                &bull; selesai {{ $import->completed_at->translatedFormat('d M Y H:i') }}
                                            @else
                                                &bull; dibuat {{ $import->created_at->translatedFormat('d M Y H:i') }}
                                            @endif
                                        </div>
                                        @if ($import->isCompleted())
                                            <div class="mt-2">
                                                <span class="text-success">{{ number_format($import->success_rows) }} berhasil</span>
                                                @if ($import->failed_rows > 0)
                                                    &middot; <span class="text-danger">{{ number_format($import->failed_rows) }} gagal</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($import->failure_message)
                                            <div class="text-danger small mt-1">{{ $import->failure_message }}</div>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $statusClasses[$import->status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $statusLabels[$import->status] ?? ucfirst($import->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
<script>
    document.querySelector('form[action*="import/preview"]')?.addEventListener('submit', function() {
        setTimeout(() => {
            const btn = document.getElementById('preview-btn');
            if (btn) {
                btn.disabled = true;
                document.getElementById('preview-btn-icon').hidden = true;
                document.getElementById('preview-spinner').hidden = false;
                document.getElementById('preview-btn-text').textContent = 'Memproses...';
            }
        }, 50);
    });
</script>
</x-app-layout>
