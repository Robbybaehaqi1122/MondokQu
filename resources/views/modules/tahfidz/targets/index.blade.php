<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Target Hafalan</h2>
                <div class="text-secondary mt-1">Kelola target hafalan Al-Quran per santri.</div>
            </div>
            <a href="{{ route('tahfidz.targets.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                Target Baru
            </a>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('tahfidz.targets.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Santri</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama atau NIS..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Santri</label>
                    <select name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santriOptions as $santri)
                            <option value="{{ $santri->id }}" @selected($filters['santri'] == $santri->id)>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Target</label>
                    <select name="type" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($typeOptions as $opt)
                            <option value="{{ $opt['value'] }}" @selected($filters['type'] === $opt['value'])>
                                {{ $opt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-filter me-1"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Target</th>
                        <th>Progress</th>
                        <th>Deadline</th>
                        <th>Catatan</th>
                        <th>Dibuat Oleh</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($targets as $target)
                        @php
                            $progress = $target->progressPercentage();
                            $barClass = $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-primary' : 'bg-warning');
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $target->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS {{ $target->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ number_format($target->target_value) }} {{ $target->typeLabel() }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height: 0.6rem;">
                                        <div class="progress-bar {{ $barClass }}" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <span class="small text-nowrap {{ $progress >= 100 ? 'text-success fw-bold' : '' }}">
                                        {{ number_format($progress, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if ($target->target_date)
                                    <span class="{{ $target->isOverdue() ? 'text-danger fw-bold' : ($target->isDeadlineNear() ? 'text-warning' : '') }}">
                                        {{ $target->target_date->translatedFormat('d M Y') }}
                                        @if ($target->isOverdue())
                                            <i class="ti ti-alert-triangle ms-1" title="Terlewat"></i>
                                        @elseif ($target->isDeadlineNear())
                                            <i class="ti ti-clock ms-1" title="Kurang dari 30 hari"></i>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary small">{{ $target->notes ?: '-' }}</span>
                            </td>
                            <td>
                                <span class="small text-secondary">{{ $target->creator?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('tahfidz.targets.edit', $target) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Edit">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('tahfidz.targets.destroy', $target) }}" onsubmit="return confirm('Hapus target ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-secondary">Belum ada target hafalan. <a href="{{ route('tahfidz.targets.create') }}">Buat target baru</a>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($targets->hasPages())
            <div class="card-footer">
                {{ $targets->links() }}
            </div>
        @endif
    </div>
</x-app-layout>