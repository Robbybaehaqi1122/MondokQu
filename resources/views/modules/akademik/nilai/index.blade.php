<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Daftar Nilai Santri</h2>
            </div>
            <a href="{{ route('akademik.nilai.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Input Nilai
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Cari santri..."
                        value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select name="mata_pelajaran_id" class="form-select">
                        <option value="">Semua Mapel</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel->id }}" {{ request('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="semester" class="form-select">
                        <option value="">Semua Semester</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="ti ti-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Mata Pelajaran</th>
                        <th>Semester</th>
                        <th>Pengetahuan</th>
                        <th>Keterampilan</th>
                        <th>Nilai Akhir</th>
                        <th>Predikat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nilais as $nilai)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $nilai->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">{{ $nilai->santri?->nis ?? '' }}</div>
                            </td>
                            <td>{{ $nilai->mataPelajaran?->nama ?? '-' }}</td>
                            <td>{{ $nilai->semester }}</td>
                            <td>{{ $nilai->nilai_pengetahuan }}</td>
                            <td>{{ $nilai->nilai_keterampilan }}</td>
                            <td>
                                <span class="fw-bold">{{ $nilai->nilai_akhir }}</span>
                                @php
                                    $kkm = $nilai->mataPelajaran?->kkm ?? 70;
                                @endphp
                                @if ($nilai->nilai_akhir >= $kkm)
                                    <i class="ti ti-check text-success"></i>
                                @else
                                    <i class="ti ti-x text-danger"></i>
                                @endif
                            </td>
                            <td>
                                @php
                                    $predikatClasses = ['A' => 'bg-success-lt text-success', 'B' => 'bg-primary-lt text-primary', 'C' => 'bg-warning-lt text-warning', 'D' => 'bg-danger-lt text-danger'];
                                @endphp
                                <span class="badge {{ $predikatClasses[$nilai->predikat] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $nilai->predikat }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('akademik.nilai.edit', $nilai) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('akademik.nilai.destroy', $nilai) }}" method="POST"
                                        onsubmit="return confirm('Hapus nilai ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-secondary text-center py-4">
                                Belum ada nilai dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($nilais->hasPages())
            <div class="card-footer">{{ $nilais->links() }}</div>
        @endif
    </div>
</x-app-layout>
