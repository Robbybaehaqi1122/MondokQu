<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Laporan Pelanggaran</h2>
            <div class="text-secondary mt-1">Rekap pelanggaran santri per periode.</div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pelanggaran.laporan.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Santri</label>
                    <select name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santriOptions as $s)
                            <option value="{{ $s->id }}" @selected($filters['santri'] == $s->id)>{{ $s->full_name }} ({{ $s->nis }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriOptions as $k)
                            <option value="{{ $k->id }}" @selected($filters['kategori'] == $k->id)>{{ $k->nama }} ({{ $k->poin }} poin)</option>
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
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter"></i></button>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="{{ route('pelanggaran.laporan.index', ['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->toDateString()]) }}" class="btn btn-outline-secondary w-100" title="Reset ke bulan ini">
                        <i class="ti ti-refresh"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Total Pelanggaran</div>
                        <div class="fs-2 fw-bold">{{ number_format($totalPelanggaran) }}</div>
                    </div>
                    <span class="avatar bg-warning-lt text-warning"><i class="ti ti-list-details"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase text-secondary small">Akumulasi Poin</div>
                        <div class="fs-2 fw-bold">{{ number_format($totalPoin) }}</div>
                    </div>
                    <span class="avatar bg-danger-lt text-danger"><i class="ti ti-numbers"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Per Santri</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Jumlah</th>
                                <th>Total Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perSantri as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->santri?->full_name ?? '-' }}</td>
                                    <td>{{ $item->jumlah }}x</td>
                                    <td class="fw-semibold {{ ((int) $item->total_poin) >= 100 ? 'text-danger' : '' }}">{{ number_format((int) $item->total_poin) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-secondary">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Per Kategori</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Total Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perKategori as $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-danger-lt text-danger">{{ $item->kategori?->nama ?? '-' }}</span>
                                    </td>
                                    <td>{{ $item->jumlah }}x</td>
                                    <td class="fw-semibold">{{ number_format((int) $item->total_poin) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-secondary">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($dailyStats->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tren Harian</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Pelanggaran</th>
                            <th>Total Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailyStats as $day)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($day->tgl)->translatedFormat('d M Y') }}</td>
                                <td>{{ $day->jumlah }}x</td>
                                <td class="fw-semibold">{{ number_format((int) $day->total_poin) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
