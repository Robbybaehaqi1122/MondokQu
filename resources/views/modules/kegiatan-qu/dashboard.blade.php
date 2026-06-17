<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
            <h2 class="page-title mt-1">Dashboard Kegiatan & Ekstrakurikuler</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Kegiatan</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($totalKegiatan) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Kegiatan Aktif</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($kegiatanAktif) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Pendaftar</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($totalPendaftar) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Pertemuan</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($totalPertemuan) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Presensi Hadir</div>
                            <div class="fs-2 fw-bold mb-0">{{ number_format($totalPresensi) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($kegiatanTerbaru->isNotEmpty())
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Kegiatan Terbaru</h3>
                        <a href="{{ route('kegiatan.kegiatan.index') }}" class="btn btn-outline-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($kegiatanTerbaru as $k)
                            <a href="{{ route('kegiatan.kegiatan.show', $k) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $k->nama }}</div>
                                        <div class="text-secondary small">
                                            {{ $k->pembina?->name ?? '-' }} &middot;
                                            <span class="badge {{ $k->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $k->status }}
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-secondary">{{ $k->created_at->diffForHumans() }}</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($pendaftaranTerbaru->isNotEmpty())
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pendaftaran Terbaru</h3>
                        <a href="{{ route('kegiatan.pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($pendaftaranTerbaru as $p)
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $p->santri?->full_name ?? '-' }}</div>
                                        <div class="text-secondary small">{{ $p->kegiatan?->nama ?? '-' }}</div>
                                    </div>
                                    <small class="text-secondary">{{ $p->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
