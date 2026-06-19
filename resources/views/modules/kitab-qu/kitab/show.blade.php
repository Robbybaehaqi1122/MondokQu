<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">{{ $kitab->nama }}</h2>
                <div class="text-secondary small">Detail kitab dan riwayat setoran hafalan.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('kitab.kitab.edit', $kitab) }}" class="btn btn-outline-primary">Edit Kitab</a>
                <a href="{{ route('kitab.kitab.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Detail Kitab</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8">{{ $kitab->nama }}</dd>
                        <dt class="col-sm-4">Pengarang</dt>
                        <dd class="col-sm-8">{{ $kitab->pengarang ?: '-' }}</dd>
                        <dt class="col-sm-4">Kategori</dt>
                        <dd class="col-sm-8">{{ $kitab->kategori?->nama ?? '-' }}</dd>
                    </dl>
                    @if ($kitab->keterangan)
                        <hr>
                        <div class="text-secondary">{{ $kitab->keterangan }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h3 class="card-title">Riwayat Setoran</h3>
                    <a href="{{ route('kitab.setoran.create') }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i> Tambah Setoran
                    </a>
                </div>
                @if ($kitab->setorans->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Santri</th>
                                    <th>Tanggal</th>
                                    <th>Materi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kitab->setorans as $setoran)
                                    <tr>
                                        <td class="fw-semibold">{{ $setoran->santri?->full_name ?? '-' }}</td>
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
                @else
                    <div class="card-body"><div class="text-secondary">Belum ada setoran untuk kitab ini.</div></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
