<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
            <h2 class="page-title mt-1">Data Seleksi</h2>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Cari calon santri..." value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select name="jenis" class="form-select">
                        <option value="">Semua Jenis</option>
                        <option value="administrasi" @selected(request('jenis') === 'administrasi')>Administrasi</option>
                        <option value="tes_baca_quran" @selected(request('jenis') === 'tes_baca_quran')>Tes Baca Quran</option>
                        <option value="wawancara" @selected(request('jenis') === 'wawancara')>Wawancara</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="hasil" class="form-select">
                        <option value="">Semua Hasil</option>
                        <option value="lulus" @selected(request('hasil') === 'lulus')>Lulus</option>
                        <option value="tidak_lulus" @selected(request('hasil') === 'tidak_lulus')>Tidak Lulus</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Calon Santri</th>
                        <th>Jenis</th>
                        <th>Nilai</th>
                        <th>Hasil</th>
                        <th>Penguji</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($seleksis as $s)
                        <tr>
                            <td>
                                <a href="{{ route('ppdb.pendaftaran.show', $s->pendaftaran) }}" class="text-reset text-decoration-none fw-semibold">
                                    {{ $s->pendaftaran?->nama_lengkap ?? '-' }}
                                </a>
                            </td>
                            <td><span class="badge bg-info">{{ $s->jenis }}</span></td>
                            <td>{{ $s->nilai ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $s->hasil === 'lulus' ? 'bg-success' : 'bg-danger' }}">{{ $s->hasil }}</span>
                            </td>
                            <td>{{ $s->penguji?->name ?? '-' }}</td>
                            <td>{{ $s->tanggal_seleksi?->translatedFormat('d M Y') ?: '-' }}</td>
                            <td class="text-end">
                                <form action="{{ route('ppdb.seleksi.destroy', $s) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-secondary">Belum ada data seleksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($seleksis->hasPages())
            <div class="card-footer">{{ $seleksis->links() }}</div>
        @endif
    </div>
</x-app-layout>
