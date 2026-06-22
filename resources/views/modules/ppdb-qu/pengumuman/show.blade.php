<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
                <h2 class="page-title mt-1">{{ $pengumuman->judul }}</h2>
                <div class="text-secondary small">{{ $pengumuman->gelombang?->nama }} &middot; {{ $pengumuman->tanggal_pengumuman->translatedFormat('d M Y') }}</div>
            </div>
            <div class="d-flex gap-2">
                @if (! $pengumuman->published_at)
                    <form action="{{ route('ppdb.pengumuman.publish', $pengumuman) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">Publikasikan</button>
                    </form>
                @else
                    <button type="button" class="btn btn-outline-info" onclick="salinLink('{{ route('ppdb.pengumuman.public', $pengumuman) }}', this)">Salin Link Publik</button>
                @endif
            </div>
        </div>
    </x-slot>

    @if ($pengumuman->deskripsi)
        <div class="card mb-3">
            <div class="card-body">{{ $pengumuman->deskripsi }}</div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Pendaftar {{ $pengumuman->gelombang?->nama }}</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th>Administrasi</th>
                        <th>Tes Quran</th>
                        <th>Wawancara</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendaftarans as $p)
                        <tr class="{{ $p->status === 'diterima' || $p->status === 'daftar_ulang' ? 'table-success' : ($p->status === 'ditolak' ? 'table-danger' : '') }}">
                            <td><code>{{ $p->nomor_pendaftaran }}</code></td>
                            <td class="fw-semibold">{{ $p->nama_lengkap }}</td>
                            <td>
                                <span class="badge {{ $p->status === 'diterima' || $p->status === 'daftar_ulang' ? 'bg-success' : ($p->status === 'ditolak' ? 'bg-danger' : 'bg-warning') }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            @foreach (['administrasi', 'tes_baca_quran', 'wawancara'] as $jenis)
                                @php
                                    $seleksi = $p->seleksis->firstWhere('jenis', $jenis);
                                @endphp
                                <td>
                                    @if ($seleksi)
                                        <span class="badge {{ $seleksi->hasil === 'lulus' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $seleksi->hasil }}
                                        </span>
                                        @if ($seleksi->nilai)
                                            <small class="text-secondary">({{ $seleksi->nilai }})</small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-secondary">Tidak ada pendaftar di gelombang ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @push('scripts')
        <script>
            function salinLink(url, btn) {
                navigator.clipboard.writeText(url).then(() => {
                    const original = btn.textContent;
                    btn.textContent = 'Tersalin!';
                    setTimeout(() => btn.textContent = original, 2000);
                }).catch(() => {});
            }
        </script>
    @endpush
</x-app-layout>
