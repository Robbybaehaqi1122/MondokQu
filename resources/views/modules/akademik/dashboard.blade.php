<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Dashboard Akademik</h2>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-6 col-lg-3">
            <div class="card card-body stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">Total Mapel</div>
                        <div class="fs-2 fw-bold">{{ number_format($totalMapel) }}</div>
                    </div>
                    <span class="avatar bg-blue-lt text-blue flex-shrink-0">
                        <i class="ti ti-books"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-body stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">Total Nilai</div>
                        <div class="fs-2 fw-bold">{{ number_format($totalNilai) }}</div>
                    </div>
                    <span class="avatar bg-azure-lt text-azure flex-shrink-0">
                        <i class="ti ti-pencil"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-body stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">Santri Dinilai</div>
                        <div class="fs-2 fw-bold">{{ number_format($totalSantriDinilai) }}</div>
                    </div>
                    <span class="avatar bg-green-lt text-green flex-shrink-0">
                        <i class="ti ti-user-check"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card card-body stat-card">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="min-width-0">
                        <div class="text-uppercase text-secondary small text-truncate">Rata-rata</div>
                        <div class="fs-2 fw-bold">{{ $rataRata }}</div>
                    </div>
                    <span class="avatar bg-orange-lt text-orange flex-shrink-0">
                        <i class="ti ti-chart-bar"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rata-rata per Mapel</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-mobile-md">
                        <thead>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th class="d-none d-md-table-cell">KKM</th>
                                <th>Rata-rata</th>
                                <th class="d-none d-md-table-cell">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nilaiPerMapel as $mapel)
                                <tr>
                                    <td>{{ $mapel->nama }}</td>
                                    <td class="d-none d-md-table-cell"><span class="badge bg-azure-lt text-azure">{{ $mapel->kkm }}</span></td>
                                    <td>
                                        @php
                                            $avg = (int) $mapel->rata_rata;
                                            $avgClass = $avg >= $mapel->kkm ? 'text-success' : 'text-danger';
                                        @endphp
                                        <span class="fw-bold {{ $avgClass }}">{{ $avg }}</span>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ number_format($mapel->total_nilai) }}</td>
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
                    <table class="table table-vcenter card-table table-mobile-md">
                        <thead>
                            <tr>
                                <th>Mata Pelajaran</th>
                                <th class="d-none d-md-table-cell">KKM</th>
                                <th>Jumlah Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mapelTerbanyak as $mapel)
                                <tr>
                                    <td>{{ $mapel->nama }}</td>
                                    <td class="d-none d-md-table-cell"><span class="badge bg-azure-lt text-azure">{{ $mapel->kkm }}</span></td>
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
                            <a href="{{ route('akademik.mata-pelajaran.index') }}" class="btn btn-outline-primary flex-fill flex-sm-grow-0">
                                <i class="ti ti-books me-1"></i> Kelola Mapel
                            </a>
                            <a href="{{ route('akademik.nilai.create') }}" class="btn btn-outline-success flex-fill flex-sm-grow-0">
                                <i class="ti ti-plus me-1"></i> Input Nilai
                            </a>
                            <a href="{{ route('akademik.nilai.index') }}" class="btn btn-outline-info flex-fill flex-sm-grow-0">
                                <i class="ti ti-list me-1"></i> Semua Nilai
                            </a>
                            <a href="{{ route('akademik.rapor.index') }}" class="btn btn-outline-warning flex-fill flex-sm-grow-0">
                                <i class="ti ti-report-analytics me-1"></i> Rapor Digital
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
