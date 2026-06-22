<x-guest-layout>
    <div class="text-center mb-4">
        <h2 class="mb-1">{{ $pengumuman->judul }}</h2>
        <div class="text-secondary">
            {{ $pengumuman->gelombang?->nama }}
            &middot;
            {{ $pengumuman->tanggal_pengumuman->translatedFormat('d F Y') }}
        </div>
    </div>

    @if ($pengumuman->deskripsi)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Informasi & Langkah Selanjutnya</h5>
                <p class="mb-0">{{ $pengumuman->deskripsi }}</p>
            </div>
        </div>
    @endif

    @php
        $lolos = $pendaftarans->whereIn('status', ['diterima', 'daftar_ulang']);
        $tidakLolos = $pendaftarans->where('status', 'ditolak');
        $menunggu = $pendaftarans->where('status', 'menunggu');
    @endphp

    @if ($lolos->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge bg-success me-2">&#10003;</span>
                    Peserta Diterima ({{ $lolos->count() }})
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>No. Pendaftaran</th><th>Nama</th></tr></thead>
                    <tbody>
                        @foreach ($lolos as $p)
                            <tr class="table-success">
                                <td><code>{{ $p->nomor_pendaftaran }}</code></td>
                                <td>{{ $p->nama_lengkap }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($tidakLolos->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge bg-danger me-2">&#10007;</span>
                    Peserta Tidak Diterima ({{ $tidakLolos->count() }})
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>No. Pendaftaran</th><th>Nama</th></tr></thead>
                    <tbody>
                        @foreach ($tidakLolos as $p)
                            <tr class="table-danger">
                                <td><code>{{ $p->nomor_pendaftaran }}</code></td>
                                <td>{{ $p->nama_lengkap }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($menunggu->isNotEmpty())
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <span class="badge bg-warning me-2">&#9888;</span>
                    Belum Ditentukan ({{ $menunggu->count() }})
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>No. Pendaftaran</th></tr></thead>
                    <tbody>
                        @foreach ($menunggu as $p)
                            <tr>
                                <td><code>{{ $p->nomor_pendaftaran }}</code></td>
                                <td>{{ $p->nama_lengkap }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-guest-layout>
