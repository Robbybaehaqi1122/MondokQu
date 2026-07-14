<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Kunjungan UKS</h2>
        </div>
        <a href="{{ route('kesehatan.pemeriksaan.create') }}" class="btn btn-primary btn-sm">Catat Pemeriksaan</a>
    </x-slot>

    <div class="row mb-3">
        <div class="col-lg-8">
            <form method="GET" action="{{ route('kesehatan.pemeriksaan.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Cari santri..." value="{{ $filters['q'] }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if ($filters['q'] || $filters['date_from'] || $filters['date_to'])
                    <div class="col-12">
                        <a href="{{ route('kesehatan.pemeriksaan.index') }}" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-mobile-md">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Santri</th>
                        <th class="d-none d-md-table-cell">Keluhan</th>
                        <th class="d-none d-md-table-cell">Diagnosis</th>
                        <th class="d-none d-md-table-cell">Rujukan</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemeriksaans as $p)
                        <tr>
                            <td>{{ $p->tanggal_pemeriksaan->translatedFormat('d M Y') }}</td>
                            <td class="fw-semibold">{{ $p->santri?->full_name ?? 'Unknown' }}</td>
                            <td class="d-none d-md-table-cell">{{ $p->keluhan }}</td>
                            <td class="d-none d-md-table-cell">{{ $p->diagnosis ?: '-' }}</td>
                            <td class="d-none d-md-table-cell">
                                @if ($p->rujukan)
                                    <span class="badge bg-warning-lt">Dirujuk</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('kesehatan.pemeriksaan.show', $p) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada pemeriksaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pemeriksaans->hasPages())
            <div class="card-footer">
                {{ $pemeriksaans->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
