<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title mt-1">Rapor Hafalan</h2>
            <div class="text-secondary small">Rekap hafalan kitab per santri.</div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <select name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santris as $s)
                            <option value="{{ $s->id }}" @selected(request('santri') == $s->id)>{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="kitab" class="form-select">
                        <option value="">Semua Kitab</option>
                        @foreach ($kitabs as $k)
                            <option value="{{ $k->id }}" @selected(request('kitab') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if (request()->anyFilled(['santri', 'kitab']))
                    <div class="col-12">
                        <a href="{{ route('kitab.setoran.rapor') }}" class="btn btn-ghost-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Rekap Hafalan</h3></div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Kitab</th>
                        <th>Total Setoran</th>
                        <th>Disetujui</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekap as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row->santri?->full_name ?? '-' }}</td>
                            <td>{{ $row->kitab?->nama ?? '-' }}</td>
                            <td>{{ number_format($row->total_setoran) }}</td>
                            <td>{{ number_format($row->disetujui) }}</td>
                            <td>
                                @php $pct = $row->total_setoran > 0 ? round(($row->disetujui / $row->total_setoran) * 100) : 0; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress w-100" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-secondary small">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-secondary">Belum ada data hafalan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($setorans->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Detail Setoran</h3></div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Kitab</th>
                            <th>Tanggal</th>
                            <th>Materi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($setorans as $setoran)
                            <tr>
                                <td class="fw-semibold">{{ $setoran->santri?->full_name ?? '-' }}</td>
                                <td>{{ $setoran->kitab?->nama ?? '-' }}</td>
                                <td class="text-secondary">{{ $setoran->tanggal_setoran?->translatedFormat('d M Y') }}</td>
                                <td>{{ $setoran->materi ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $setoran->status === 'disetujui' ? 'bg-success' : ($setoran->status === 'ditolak' ? 'bg-danger' : 'bg-warning-lt') }}">
                                        {{ ucfirst($setoran->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($setorans->hasPages())
                <div class="card-footer d-flex justify-content-center">{{ $setorans->links() }}</div>
            @endif
        </div>
    @endif
</x-app-layout>
