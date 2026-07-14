<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Laporan Kesehatan</h2>
        </div>
    </x-slot>

    <div class="row mb-3">
        <div class="col-lg-6">
            <form method="GET" action="{{ route('kesehatan.laporan.index') }}" class="row g-2">
                <div class="col-md-5">
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-5">
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-bold">Pemeriksaan</div>
                    <div class="fs-2 fw-bold mb-0">{{ number_format($totalPemeriksaan) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-secondary small text-uppercase fw-bold">Rujukan</div>
                    <div class="fs-2 fw-bold mb-0">{{ number_format($totalRujukan) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if ($santriDenganKondisiKhusus->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Santri dengan Kondisi Khusus</h3>
                <div class="text-secondary small">Memiliki riwayat penyakit atau alergi.</div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Riwayat Penyakit</th>
                            <th class="d-none d-md-table-cell">Alergi Obat</th>
                            <th class="d-none d-md-table-cell">Alergi Makanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($santriDenganKondisiKhusus as $santri)
                            <tr>
                                <td class="fw-semibold">{{ $santri->full_name }} ({{ $santri->nis }})</td>
                                <td>{{ $santri->rekamMedis?->riwayat_penyakit ?: '-' }}</td>
                                <td class="d-none d-md-table-cell">{{ $santri->rekamMedis?->alergi_obat ?: '-' }}</td>
                                <td class="d-none d-md-table-cell">{{ $santri->rekamMedis?->alergi_makanan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($imunisasiPerSantri->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Riwayat Imunisasi</h3>
                <div class="text-secondary small">Periode {{ $dateFrom }} s/d {{ $dateTo }}.</div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th class="d-none d-md-table-cell">Jenis Imunisasi</th>
                            <th class="d-none d-md-table-cell">Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($imunisasiPerSantri as $santriName => $imunisasis)
                            @foreach ($imunisasis as $imunisasi)
                                <tr>
                                    <td class="fw-semibold">{{ $santriName }}</td>
                                    <td class="d-none d-md-table-cell">{{ $imunisasi->jenis_imunisasi }}</td>
                                    <td class="d-none d-md-table-cell">{{ $imunisasi->tanggal?->translatedFormat('d M Y') ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $imunisasi->status === 'sudah' ? 'bg-success' : 'bg-warning' }}">
                                            {{ $imunisasi->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($obatExpired->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Obat Akan Expired / Sudah Expired</h3>
                <div class="text-secondary small">1 bulan ke depan.</div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Nama Obat</th>
                            <th class="d-none d-md-table-cell">Stok</th>
                            <th class="d-none d-md-table-cell">Expired Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($obatExpired as $obat)
                            <tr class="{{ $obat->expired_date?->isPast() ? 'table-danger' : 'table-warning' }}">
                                <td class="fw-semibold">{{ $obat->nama_obat }}</td>
                                <td class="d-none d-md-table-cell">{{ $obat->stok }} {{ $obat->satuan }}</td>
                                <td class="d-none d-md-table-cell">{{ $obat->expired_date?->translatedFormat('d M Y') }}</td>
                                <td>
                                    @if ($obat->expired_date?->isPast())
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-warning">Akan Expired</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($santriDenganKondisiKhusus->isEmpty() && $imunisasiPerSantri->isEmpty() && $obatExpired->isEmpty())
        <div class="card">
            <div class="card-body text-secondary">Belum ada data laporan untuk periode ini.</div>
        </div>
    @endif
</x-app-layout>
