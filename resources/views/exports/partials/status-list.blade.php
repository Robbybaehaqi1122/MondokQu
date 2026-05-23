@php
    $statusClasses = [
        'queued' => 'bg-warning-lt text-warning',
        'processing' => 'bg-azure-lt text-azure',
        'completed' => 'bg-success-lt text-success',
        'failed' => 'bg-danger-lt text-danger',
    ];
    $statusLabels = [
        'queued' => 'Menunggu Queue',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'failed' => 'Gagal',
    ];
    $progressValues = [
        'queued' => 25,
        'processing' => 70,
        'completed' => 100,
        'failed' => 100,
    ];
    $progressClasses = [
        'queued' => 'bg-warning',
        'processing' => 'bg-azure',
        'completed' => 'bg-success',
        'failed' => 'bg-danger',
    ];
    $activeExportCount = collect($dataExports ?? collect())
        ->whereIn('status', ['queued', 'processing'])
        ->count();
@endphp

@if (($dataExports ?? collect())->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ $title ?? 'Export Terbaru' }}</h3>
                <div class="text-secondary small mt-2">File background export akan tersedia di sini setelah job queue selesai.</div>
            </div>
        </div>
        @if ($activeExportCount > 0)
            <div class="card-body border-bottom">
                <div class="alert alert-info mb-0 d-flex flex-column flex-md-row gap-3 align-items-md-center" role="status">
                    <span class="status status-azure status-indicator">
                        <span class="status-dot status-dot-animated"></span>
                    </span>
                    <div>
                        <div class="fw-semibold">{{ number_format($activeExportCount) }} export sedang diproses</div>
                        <div class="small">File akan muncul sebagai tombol download setelah background queue selesai.</div>
                    </div>
                </div>
            </div>
        @endif
        <div class="list-group list-group-flush">
            @foreach ($dataExports as $dataExport)
                @php
                    $progressValue = $progressValues[$dataExport->status] ?? 0;
                    $progressClass = $progressClasses[$dataExport->status] ?? 'bg-secondary';
                    $statusLabel = $statusLabels[$dataExport->status] ?? ucfirst($dataExport->status);
                @endphp
                <div class="list-group-item">
                    <div class="d-flex flex-column flex-lg-row justify-content-lg-between gap-3">
                        <div class="flex-fill">
                            <div class="fw-semibold">{{ $dataExport->name }}</div>
                            <div class="text-secondary small mt-1">
                                {{ number_format($dataExport->row_count) }} baris
                                @if ($dataExport->completed_at)
                                    &bull; selesai {{ $dataExport->completed_at->translatedFormat('d M Y H:i') }}
                                @else
                                    &bull; dibuat {{ $dataExport->created_at->translatedFormat('d M Y H:i') }}
                                @endif
                            </div>
                            @if ($dataExport->failure_message)
                                <div class="text-danger small mt-1">{{ $dataExport->failure_message }}</div>
                            @endif
                            <div class="export-progress mt-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-secondary">{{ $statusLabel }}</span>
                                    <span class="fw-semibold">{{ $progressValue }}%</span>
                                </div>
                                <div class="progress progress-sm" role="progressbar" aria-label="{{ $dataExport->name }} {{ $statusLabel }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progressValue }}">
                                    <div class="progress-bar {{ $progressClass }} @if (in_array($dataExport->status, ['queued', 'processing'], true)) progress-bar-indeterminate @endif" style="width: {{ $progressValue }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $statusClasses[$dataExport->status] ?? 'bg-secondary-lt text-secondary' }}">
                                {{ $statusLabel }}
                            </span>
                            @if ($dataExport->isCompleted())
                                <a href="{{ route('exports.download', $dataExport) }}" class="btn btn-sm btn-outline-primary">
                                    Download
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
