<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Detail Nilai Sikap</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }} &middot; Semester {{ $semester }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('akademik.attitude.create', ['santri_id' => $santri->id, 'semester' => $semester]) }}" class="btn btn-primary">
                    <i class="ti ti-edit me-1"></i> Edit
                </a>
                <a href="{{ route('akademik.attitude.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Nilai Sikap Spiritual & Sosial</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Aspek</th>
                        <th>Predikat</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sc = ['SB' => 'bg-success-lt text-success', 'B' => 'bg-primary-lt text-primary', 'C' => 'bg-warning-lt text-warning', 'K' => 'bg-danger-lt text-danger'];
                        $predicateLabels = ['SB' => 'Sangat Baik', 'B' => 'Baik', 'C' => 'Cukup', 'K' => 'Kurang'];
                    @endphp
                    <tr><td colspan="3" class="fw-bold text-primary">Sikap Spiritual (Akhlak kepada Allah)</td></tr>
                    @forelse (($grades['spiritual'] ?? collect()) as $g)
                        <tr>
                            <td style="padding-left:2rem">{{ $g->aspect_name }}</td>
                            <td>
                                <span class="badge {{ $sc[$g->predicate] ?? '' }}">
                                    {{ $g->predicate }} - {{ $predicateLabels[$g->predicate] ?? $g->predicate }}
                                </span>
                            </td>
                            <td>{{ $g->description ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-secondary" style="padding-left:2rem">Belum ada data.</td></tr>
                    @endforelse
                    <tr><td colspan="3" class="fw-bold text-success">Sikap Sosial (Akhlak kepada Sesama)</td></tr>
                    @forelse (($grades['sosial'] ?? collect()) as $g)
                        <tr>
                            <td style="padding-left:2rem">{{ $g->aspect_name }}</td>
                            <td>
                                <span class="badge {{ $sc[$g->predicate] ?? '' }}">
                                    {{ $g->predicate }} - {{ $predicateLabels[$g->predicate] ?? $g->predicate }}
                                </span>
                            </td>
                            <td>{{ $g->description ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-secondary" style="padding-left:2rem">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
