<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Dashboard Akademik</h2>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Total Mata Pelajaran</div>
                    <div class="fs-1 fw-bold mt-2">{{ number_format($totalMapel) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Total Nilai Tercatat</div>
                    <div class="fs-1 fw-bold mt-2">{{ number_format($totalNilai) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Santri Dinilai</div>
                    <div class="fs-1 fw-bold mt-2">{{ number_format($totalSantriDinilai) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small">Rata-rata Nilai</div>
                    <div class="fs-1 fw-bold mt-2">{{ $rataRata }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rata-rata Nilai per Mata Pelajaran</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th>KKM</th>
                                <th>Rata-rata</th>
                                <th>Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nilaiPerMapel as $mapel)
                                <tr>
                                    <td>{{ $mapel->nama }}</td>
                                    <td><span class="badge bg-azure-lt text-azure">{{ $mapel->kkm }}</span></td>
                                    <td>
                                        @php
                                            $avg = (int) $mapel->rata_rata;
                                            $avgClass = $avg >= $mapel->kkm ? 'text-success' : 'text-danger';
                                        @endphp
                                        <span class="fw-bold {{ $avgClass }}">{{ $avg }}</span>
                                    </td>
                                    <td>{{ number_format($mapel->total_nilai) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-secondary">Belum ada data nilai.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Mata Pelajaran Teraktif</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th>KKM</th>
                                <th>Jumlah Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mapelTerbanyak as $mapel)
                                <tr>
                                    <td>{{ $mapel->nama }}</td>
                                    <td><span class="badge bg-azure-lt text-azure">{{ $mapel->kkm }}</span></td>
                                    <td>{{ number_format($mapel->nilai_santris_count) }}</td>
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

    @if ($semesters->isNotEmpty())
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Akses Cepat</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('akademik.mata-pelajaran.index') }}" class="btn btn-outline-primary">
                                <i class="ti ti-books me-1"></i> Kelola Mata Pelajaran
                            </a>
                            <a href="{{ route('akademik.nilai.create') }}" class="btn btn-outline-success">
                                <i class="ti ti-plus me-1"></i> Input Nilai Baru
                            </a>
                            <a href="{{ route('akademik.nilai.index') }}" class="btn btn-outline-info">
                                <i class="ti ti-list me-1"></i> Lihat Semua Nilai
                            </a>
                            <a href="{{ route('akademik.rapor.index') }}" class="btn btn-outline-warning">
                                <i class="ti ti-report-analytics me-1"></i> Rapor Digital
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
