<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Nilai Akademik</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }} &middot; NIS {{ $santri->nis }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('wali-santri.profil-santri', $santri) }}" class="btn btn-outline-primary">
                    <i class="ti ti-user me-1"></i> Profil
                </a>
                <a href="{{ route('wali-santri.rapor', $santri) }}" class="btn btn-outline-warning">
                    <i class="ti ti-report-analytics me-1"></i> Rapor
                </a>
                <a href="{{ route('wali-santri.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $predikatClasses = ['A' => 'bg-success-lt text-success', 'B' => 'bg-primary-lt text-primary', 'C' => 'bg-warning-lt text-warning', 'D' => 'bg-danger-lt text-danger'];
    @endphp

    @foreach ($semesters as $sem)
        @php
            $semNilais = $nilais->where('semester', $sem);
        @endphp
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Semester {{ $sem }}</h3>
                <div class="card-actions">
                    <span class="text-secondary small">{{ $semNilais->count() }} mapel</span>
                </div>
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
                            <th>Predikat</th>
                            <th>Ket.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($semNilais as $nilai)
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
                                        <span class="badge bg-danger-lt text-danger">TT</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-secondary">Belum ada nilai untuk semester ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</x-app-layout>
