<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Rapor Hafalan</h2>
                <div class="text-secondary mt-1">Lihat progress hafalan Al-Quran santri per periode.</div>
            </div>
            @if ($raporData && $raporData->santri)
                <a href="{{ route('tahfidz.rapor.pdf', request()->only(['santri', 'date_from', 'date_to'])) }}"
                   class="btn btn-primary" target="_blank">
                    <i class="ti ti-file-type-pdf me-1"></i>
                    Export PDF Santri
                </a>
            @endif
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="text-nowrap fw-semibold">Export Per Kelas:</div>
                <form method="GET" action="{{ route('tahfidz.rapor.pdf-batch') }}" class="row g-2 align-items-end flex-fill" target="_blank">
                    <div class="col-md-4">
                        <select name="room" class="form-select" required>
                            <option value="">Pilih Kelas</option>
                            @foreach ($roomOptions as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_from" class="form-control" placeholder="Dari">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date_to" class="form-control" placeholder="Sampai">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="ti ti-file-type-pdf me-1"></i>
                            Export PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('tahfidz.rapor.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari Santri</label>
                    <input type="text" name="q" class="form-control" placeholder="Nama atau NIS..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pilih Santri</label>
                    <select name="santri" class="form-select" required>
                        <option value="">Pilih Santri</option>
                        @foreach ($santriOptions as $santri)
                            <option value="{{ $santri->id }}" @selected($filters['santri'] == $santri->id)>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($raporData && $raporData->santri)
        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-secondary small">Total Setoran</div>
                            <div class="fs-2 fw-bold">{{ number_format($raporData->total_sessions) }}</div>
                        </div>
                        <span class="avatar bg-primary-lt text-primary">
                            <i class="ti ti-clipboard-list"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-secondary small">Total Ayat</div>
                            <div class="fs-2 fw-bold">{{ number_format($raporData->total_ayat) }}</div>
                        </div>
                        <span class="avatar bg-azure-lt text-azure">
                            <i class="ti ti-book-2"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-secondary small">Lancar</div>
                            <div class="fs-2 fw-bold text-success">{{ number_format($raporData->total_lancar) }}</div>
                        </div>
                        <span class="avatar bg-success-lt text-success">
                            <i class="ti ti-check"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-secondary small">Perlu Pengulangan</div>
                            <div class="fs-2 fw-bold text-warning">{{ number_format($raporData->total_perlu_pengulangan) }}</div>
                        </div>
                        <span class="avatar bg-warning-lt text-warning">
                            <i class="ti ti-alert-triangle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if ($raporData->targets->isNotEmpty())
            @php
                $avgProgress = round($raporData->targets->avg(fn ($t) => $t->progressPercentage()), 1);
                $achievedCount = $raporData->targets->filter(fn ($t) => $t->progressPercentage() >= 100)->count();
            @endphp
            <div class="row row-cards mb-3">
                <div class="col-sm-6 col-lg-6">
                    <div class="card card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="text-uppercase text-secondary small">Rata-rata Progress Target</div>
                                <div class="fs-2 fw-bold {{ $avgProgress >= 100 ? 'text-success' : ($avgProgress >= 50 ? 'text-primary' : 'text-warning') }}">
                                    {{ number_format($avgProgress, 1) }}%
                                </div>
                            </div>
                            <span class="avatar bg-primary-lt text-primary">
                                <i class="ti ti-bullseye"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-6">
                    <div class="card card-body">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <div class="text-uppercase text-secondary small">Target Tercapai</div>
                                <div class="fs-2 fw-bold text-success">{{ number_format($achievedCount) }}/{{ number_format($raporData->targets->count()) }}</div>
                            </div>
                            <span class="avatar bg-success-lt text-success">
                                <i class="ti ti-check"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($raporData->targets->isNotEmpty())
            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                        <div>
                            <h3 class="card-title">Target Hafalan</h3>
                            <div class="text-secondary small mt-1">Progress capaian hafalan (evaluasi Lancar) terhadap target yang ditetapkan.</div>
                        </div>
                        <a href="{{ route('tahfidz.targets.create', ['santri_id' => $raporData->santri->id]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>
                            Tambah Target
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Target</th>
                                <th>Progress</th>
                                <th>Deadline</th>
                                <th>Catatan</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($raporData->targets as $target)
                                @php
                                    $progress = $target->progressPercentage();
                                    $current = $target->computeProgress();
                                    $barClass = $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-primary' : 'bg-warning');
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ number_format($target->target_value) }} {{ $target->typeLabel() }}</span>
                                    </td>
                                    <td style="min-width: 200px;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-fill" style="height: 0.7rem;">
                                                <div class="progress-bar {{ $barClass }}" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="small text-nowrap fw-semibold {{ $progress >= 100 ? 'text-success' : '' }}">
                                                {{ number_format($current) }}/{{ number_format($target->target_value) }}
                                                ({{ number_format($progress, 1) }}%)
                                            </span>
                                        </div>
                                        @if ($progress >= 100)
                                            <div class="text-success small mt-1">
                                                <i class="ti ti-check"></i> Tercapai
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($target->target_date)
                                            <span class="{{ $target->isOverdue() ? 'text-danger fw-bold' : ($target->isDeadlineNear() ? 'text-warning' : '') }}">
                                                {{ $target->target_date->translatedFormat('d M Y') }}
                                                @if ($target->isOverdue())
                                                    <br><span class="text-danger small">Terlewat</span>
                                                @elseif ($target->isDeadlineNear())
                                                    <br><span class="text-warning small">< 30 hari</span>
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
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('tahfidz.targets.edit', $target) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Edit target">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                            <form method="POST" action="{{ route('tahfidz.targets.destroy', $target) }}"
                                                  onsubmit="return confirm('Hapus target ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Hapus target">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2 w-100">
                    <div>
                        <h3 class="card-title">Riwayat Setoran</h3>
                        <div class="text-secondary small mt-1">
                            Menampilkan {{ $raporData->total_sessions }} setoran untuk
                            <strong>{{ $raporData->santri->full_name }}</strong>
                            (NIS {{ $raporData->santri->nis }})
                            @if ($raporData->date_from || $raporData->date_to)
                                periode
                                @if ($raporData->date_from) {{ \Carbon\Carbon::parse($raporData->date_from)->translatedFormat('d M Y') }} @endif
                                -
                                @if ($raporData->date_to) {{ \Carbon\Carbon::parse($raporData->date_to)->translatedFormat('d M Y') }} @endif
                            @else
                                sepanjang waktu
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('tahfidz.rapor.pdf', request()->only(['santri', 'date_from', 'date_to'])) }}"
                       class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="ti ti-file-type-pdf me-1"></i>
                        Export PDF
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Musyrif</th>
                            <th>Ayat</th>
                            <th>Penilaian</th>
                            <th class="w-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($raporData->sessions as $session)
                            <tr>
                                <td>{{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td>{{ $session->musyrif?->name ?? '-' }}</td>
                                <td>
                                    @foreach ($session->records as $record)
                                        <div class="small">{{ $record->surah?->name ?? '-' }}: {{ $record->verseRangeLabel() }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($session->records as $record)
                                        @php
                                            $badge = match ($record->evaluation) {
                                                'lancar' => 'bg-success-lt text-success',
                                                'perlu_pengulangan' => 'bg-warning-lt text-warning',
                                                'belum_lancar' => 'bg-danger-lt text-danger',
                                                default => 'bg-secondary-lt text-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $record->evaluationLabel() }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('tahfidz.setoran.show', $session) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-secondary">Belum ada setoran untuk santri ini pada periode yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($filters['santri'] !== '')
        <div class="alert alert-info">
            Santri tidak ditemukan atau tidak memiliki akses.
        </div>
    @else
        <div class="alert alert-secondary">
            Pilih santri untuk melihat rapor hafalan.
        </div>
    @endif
</x-app-layout>
