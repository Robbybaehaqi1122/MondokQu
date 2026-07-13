<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Nilai Sikap (Akhlak)</h2>
                <div class="text-secondary mt-1">Penilaian sikap spiritual & sosial santri</div>
            </div>
            <a href="{{ route('akademik.nilai-sikap.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Input Nilai Sikap
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="q" class="form-control" placeholder="Cari santri..."
                        value="{{ request('q') }}">
                </div>
                <div class="col-md-4">
                    <select name="semester" class="form-select">
                        <option value="">Semua Semester</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
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
                        <th>Semester</th>
                        <th>Sikap Spiritual</th>
                        <th>Sikap Sosial</th>
                        <th>Catatan Wali</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $predikatBadges = ['SB' => 'bg-success-lt text-success', 'B' => 'bg-primary-lt text-primary', 'C' => 'bg-warning-lt text-warning', 'K' => 'bg-danger-lt text-danger'];
                    @endphp
                    @forelse ($nilais as $nilai)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $nilai->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">{{ $nilai->santri?->nis ?? '' }}</div>
                            </td>
                            <td>{{ $nilai->semester }}</td>
                            <td>
                                @if ($nilai->sikap_spiritual)
                                    <span class="badge {{ $predikatBadges[$nilai->sikap_spiritual] ?? '' }}">
                                        {{ $nilai->sikap_spiritual }} - {{ $nilai->sikapSpiritualLabel() }}
                                    </span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($nilai->sikap_sosial)
                                    <span class="badge {{ $predikatBadges[$nilai->sikap_sosial] ?? '' }}">
                                        {{ $nilai->sikap_sosial }} - {{ $nilai->sikapSosialLabel() }}
                                    </span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ Str::limit($nilai->catatan_wali, 50) ?? '-' }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a href="{{ route('akademik.nilai-sikap.show', ['santri_id' => $nilai->santri_id, 'semester' => $nilai->semester]) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <a href="{{ route('akademik.nilai-sikap.edit', $nilai) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form action="{{ route('akademik.nilai-sikap.destroy', $nilai) }}" method="POST"
                                        onsubmit="return confirm('Hapus nilai sikap ini?')">
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
                            <td colspan="6" class="text-secondary text-center py-4">
                                Belum ada nilai sikap dicatat.
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
