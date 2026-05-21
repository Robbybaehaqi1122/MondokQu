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
@endphp

@if (($dataExports ?? collect())->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ $title ?? 'Export Terbaru' }}</h3>
                <div class="text-secondary small mt-2">File background export akan tersedia di sini setelah job queue selesai.</div>
            </div>
        </div>
        <div class="list-group list-group-flush">
            @foreach ($dataExports as $dataExport)
                <div class="list-group-item">
                    <div class="d-flex flex-column flex-lg-row justify-content-lg-between gap-2">
                        <div>
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
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ $statusClasses[$dataExport->status] ?? 'bg-secondary-lt text-secondary' }}">
                                {{ $statusLabels[$dataExport->status] ?? ucfirst($dataExport->status) }}
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
