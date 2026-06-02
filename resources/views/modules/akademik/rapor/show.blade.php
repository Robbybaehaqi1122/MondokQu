<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Rapor Digital</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }} &middot; Semester {{ $semester }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('akademik.rapor.pdf', ['santri_id' => $santri->id, 'semester' => $semester]) }}"
                    class="btn btn-outline-danger">
                    <i class="ti ti-file-download me-1"></i> Download PDF
                </a>
                <a href="{{ route('akademik.rapor.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Identitas Santri --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Identitas Santri</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>Nama</strong><br>{{ $santri->full_name }}</div>
                <div class="col-md-2"><strong>NIS</strong><br>{{ $santri->nis }}</div>
                <div class="col-md-2"><strong>Kamar</strong><br>{{ $santri->displayRoomName() }}</div>
                <div class="col-md-2"><strong>Semester</strong><br>{{ $semester }}</div>
                <div class="col-md-3"><strong>Wali Santri</strong><br>{{ $santri->displayGuardianName() ?: '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Grafik Perkembangan Nilai</h3>
        </div>
        <div class="card-body">
            <div class="chart-container" style="height: 300px;">
                <canvas id="rapor-chart"></canvas>
            </div>
        </div>
    </div>

    {{-- Nilai Akademik --}}
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Nilai Akademik</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Mata Pelajaran</th>
                        <th>KKM</th>
                        <th>Pengetahuan</th>
                        <th>Keterampilan</th>
                        <th>Nilai Akhir</th>
                        <th>Rata-rata Kelas</th>
                        <th>Predikat</th>
                        <th>Ket.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nilais as $nilai)
                        @php
                            $kkm = $nilai->mataPelajaran?->kkm ?? 70;
                            $na = $nilai->nilai_akhir;
                            $rk = (int) ($rataRataKelas[$nilai->mata_pelajaran_id] ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $nilai->mataPelajaran?->nama ?? '-' }}</td>
                            <td><span class="badge bg-azure-lt text-azure">{{ $kkm }}</span></td>
                            <td>{{ $nilai->nilai_pengetahuan }}</td>
                            <td>{{ $nilai->nilai_keterampilan }}</td>
                            <td class="fw-bold {{ $na >= $kkm ? 'text-success' : 'text-danger' }}">{{ $na }}</td>
                            <td>{{ $rk }}</td>
                            <td>
                                @php
                                    $pc = ['A' => 'bg-success-lt text-success', 'B' => 'bg-primary-lt text-primary', 'C' => 'bg-warning-lt text-warning', 'D' => 'bg-danger-lt text-danger'];
                                @endphp
                                <span class="badge {{ $pc[$nilai->predikat] ?? '' }}">{{ $nilai->predikat }}</span>
                            </td>
                            <td>
                                @if ($na >= $kkm)
                                    <span class="badge bg-success-lt text-success">Tuntas</span>
                                @else
                                    <span class="badge bg-danger-lt text-danger">Tidak Tuntas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-secondary">Belum ada nilai akademik untuk semester ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        {{-- Tahfidz --}}
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Tahfidz</h3>
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-secondary small">Total Ayat</div>
                            <div class="fs-2 fw-bold mt-1">{{ number_format((int) ($tahfidzStats?->total_ayat ?? 0)) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary small">Total Sesi Setoran</div>
                            <div class="fs-2 fw-bold mt-1">{{ number_format((int) ($tahfidzStats?->total_record ?? 0)) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pelanggaran --}}
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Pelanggaran</h3>
                </div>
                <div class="card-body text-center">
                    <div class="text-secondary small">Total Poin Pelanggaran</div>
                    @php
                        $poinClass = $totalPoinPelanggaran > 50 ? 'text-danger' : ($totalPoinPelanggaran > 20 ? 'text-warning' : 'text-success');
                    @endphp
                    <div class="fs-2 fw-bold mt-1 {{ $poinClass }}">{{ number_format($totalPoinPelanggaran) }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('rapor-chart');
        if (!canvas) return;

        fetch('{{ route("akademik.rapor.chart-data") }}?santri_id={{ $santri->id }}')
            .then(r => r.json())
            .then(data => {
                if (data.semesters.length === 0 || data.series.length === 0) {
                    canvas.parentElement.innerHTML = '<div class="text-secondary text-center py-4">Belum cukup data untuk grafik.</div>';
                    return;
                }

                const colors = ['#206bc4', '#2fb344', '#d63939', '#f59f00', '#17a2b8', '#6f42c1', '#e83e8c', '#20c997'];

                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: data.semesters,
                        datasets: data.series.map((s, i) => ({
                            label: s.name,
                            data: s.data,
                            borderColor: colors[i % colors.length],
                            backgroundColor: colors[i % colors.length] + '20',
                            fill: false,
                            tension: 0.3,
                            spanGaps: true,
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: {
                            y: { min: 0, max: 100, title: { display: true, text: 'Nilai' } }
                        }
                    }
                });
            });
    });
</script>
@endpush
