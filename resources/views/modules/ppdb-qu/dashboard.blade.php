<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
            <h2 class="page-title mt-1">Dashboard PPDB</h2>
        </div>
    </x-slot>

    <div class="row row-cards">
        @if ($summary)
            <div class="col-12">
                <div class="row g-3">
                    <div class="col-sm-6 col-xl-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-secondary small text-uppercase fw-bold">Total Gelombang</div>
                                <div class="fs-2 fw-bold mb-0">{{ number_format($summary['totalGelombang']) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-secondary small text-uppercase fw-bold">Gelombang Aktif</div>
                                <div class="fs-2 fw-bold mb-0">{{ number_format($summary['gelombangAktif']) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-secondary small text-uppercase fw-bold">Total Pendaftar</div>
                                <div class="fs-2 fw-bold mb-0">{{ number_format($summary['totalPendaftar']) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-secondary small text-uppercase fw-bold">Menunggu</div>
                                <div class="fs-2 fw-bold text-warning mb-0">{{ number_format($summary['menunggu']) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-secondary small text-uppercase fw-bold">Diterima</div>
                                <div class="fs-2 fw-bold text-success mb-0">{{ number_format($summary['diterima']) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-secondary small text-uppercase fw-bold">Daftar Ulang</div>
                                <div class="fs-2 fw-bold text-info mb-0">{{ number_format($summary['daftarUlang']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (!empty($statsPerGelombang))
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Gelombang & Pendaftar</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Gelombang</th>
                                    <th>Periode</th>
                                    <th>Pendaftar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statsPerGelombang as $g)
                                    <tr>
                                        <td class="fw-semibold">{{ $g['nama'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($g['tanggal_mulai'])->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($g['tanggal_selesai'])->translatedFormat('d M Y') }}</td>
                                        <td>{{ number_format($g['pendaftarans_count']) }}</td>
                                        <td>
                                            <span class="badge {{ $g['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $g['status'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($recentPendaftarans->isNotEmpty())
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pendaftaran Terbaru</h3>
                        <a href="{{ route('ppdb.pendaftaran.index') }}" class="btn btn-outline-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($recentPendaftarans as $p)
                            <a href="{{ route('ppdb.pendaftaran.show', $p) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $p->nama_lengkap }}</div>
                                        <div class="text-secondary small">
                                            {{ $p->nomor_pendaftaran }} &middot; {{ $p->gelombang?->nama }}
                                        </div>
                                    </div>
                                    <span class="badge ms-2 {{ $p->status === 'diterima' || $p->status === 'daftar_ulang' ? 'bg-success' : ($p->status === 'ditolak' ? 'bg-danger' : 'bg-warning') }}">
                                        {{ $p->status }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
