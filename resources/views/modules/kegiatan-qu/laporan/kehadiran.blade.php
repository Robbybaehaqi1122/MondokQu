<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
            <h2 class="page-title mt-1">Laporan Kehadiran</h2>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <select name="kegiatan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih Kegiatan</option>
                        @foreach ($kegiatans as $k)
                            <option value="{{ $k->id }}" @selected($selectedKegiatan?->id === $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedKegiatan)
        <div class="row row-cards mb-3">
            <div class="col-6 col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-secondary small text-uppercase fw-bold">Total Hadir</div>
                        <div class="fs-2 fw-bold text-success">{{ number_format($rekap['totalHadir'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-secondary small text-uppercase fw-bold">Sakit</div>
                        <div class="fs-2 fw-bold text-warning">{{ number_format($rekap['totalSakit'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-secondary small text-uppercase fw-bold">Izin</div>
                        <div class="fs-2 fw-bold text-info">{{ number_format($rekap['totalIzin'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-secondary small text-uppercase fw-bold">Alpha</div>
                        <div class="fs-2 fw-bold text-danger">{{ number_format($rekap['totalAlpha'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if (!empty($rekap['pertemuans']) && $rekap['pertemuans']->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Per Pertemuan</h3>
                    <div class="text-secondary small">{{ $selectedKegiatan->nama }}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Materi</th>
                                <th>Hadir</th>
                                <th>Sakit</th>
                                <th>Izin</th>
                                <th>Alpha</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rekap['pertemuans'] as $pertemuan)
                                @php
                                    $h = $pertemuan->presensis->where('status', 'hadir')->count();
                                    $s = $pertemuan->presensis->where('status', 'sakit')->count();
                                    $i = $pertemuan->presensis->where('status', 'izin')->count();
                                    $a = $pertemuan->presensis->where('status', 'alpha')->count();
                                @endphp
                                <tr>
                                    <td>{{ $pertemuan->tanggal->translatedFormat('d M Y') }}</td>
                                    <td>{{ $pertemuan->materi ?: '-' }}</td>
                                    <td><span class="text-success fw-semibold">{{ $h }}</span></td>
                                    <td><span class="text-warning">{{ $s }}</span></td>
                                    <td><span class="text-info">{{ $i }}</span></td>
                                    <td><span class="text-danger">{{ $a }}</span></td>
                                    <td class="fw-semibold">{{ $h + $s + $i + $a }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @else
        <div class="card">
            <div class="card-body text-secondary">Pilih kegiatan untuk melihat laporan kehadiran.</div>
        </div>
    @endif
</x-app-layout>
