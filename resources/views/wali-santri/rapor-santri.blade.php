<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Rapor Santri</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }} &middot; NIS {{ $santri->nis }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('wali-santri.akademik', $santri) }}" class="btn btn-outline-info">
                    <i class="ti ti-list me-1"></i> Nilai Akademik
                </a>
                <a href="{{ route('wali-santri.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Pilih Semester</label>
                    <select name="semester" class="form-select" onchange="this.form.submit()">
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem }}" {{ $semester == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if ($semester && $nilais->isNotEmpty())
        @php
            $predikatClasses = ['A' => 'bg-success-lt text-success', 'B' => 'bg-primary-lt text-primary', 'C' => 'bg-warning-lt text-warning', 'D' => 'bg-danger-lt text-danger'];
        @endphp

        {{-- Identitas --}}
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Identitas Santri</h3></div>
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

        {{-- Nilai Akademik --}}
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title">Nilai Akademik</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>KKM</th>
                            <th>Pengetahuan</th>
                            <th>Keterampilan</th>
                            <th>Nilai Akhir</th>
                            <th>Predikat</th>
                            <th>Ket.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($nilais as $nilai)
                            @php
                                $kkm = $nilai->mataPelajaran?->kkm ?? 70;
                                $na = $nilai->nilai_akhir;
                            @endphp
                            <tr>
                                <td>{{ $nilai->mataPelajaran?->nama ?? '-' }}</td>
                                <td><span class="badge bg-azure-lt text-azure">{{ $kkm }}</span></td>
                                <td>{{ $nilai->nilai_pengetahuan }}</td>
                                <td>{{ $nilai->nilai_keterampilan }}</td>
                                <td class="fw-bold {{ $na >= $kkm ? 'text-success' : 'text-danger' }}">{{ $na }}</td>
                                <td><span class="badge {{ $predikatClasses[$nilai->predikat] ?? '' }}">{{ $nilai->predikat }}</span></td>
                                <td>
                                    @if ($na >= $kkm)
                                        <span class="badge bg-success-lt text-success">Tuntas</span>
                                    @else
                                        <span class="badge bg-danger-lt text-danger">Tidak Tuntas</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Ringkasan Tahfidz</h3></div>
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-secondary small">Total Ayat</div>
                                <div class="fs-2 fw-bold mt-1">{{ number_format((int) ($tahfidzStats?->total_ayat ?? 0)) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-secondary small">Sesi Setoran</div>
                                <div class="fs-2 fw-bold mt-1">{{ number_format((int) ($tahfidzStats?->total_record ?? 0)) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Ringkasan Pelanggaran</h3></div>
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
    @elseif ($semester && $nilais->isEmpty())
        <div class="card">
            <div class="card-body text-secondary text-center py-4">
                Belum ada nilai akademik untuk semester {{ $semester }}.
            </div>
        </div>
    @endif
</x-app-layout>
