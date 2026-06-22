<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
                <h2 class="page-title mt-1">Pengumuman</h2>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-pengumuman">
                <i class="ti ti-plus"></i> Buat Pengumuman
            </button>
        </div>
    </x-slot>

    <div class="row row-cards">
        @forelse ($pengumumans as $p)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $p->judul }}</h3>
                        @if ($p->published_at)
                            <span class="badge bg-success ms-auto">Published</span>
                        @else
                            <span class="badge bg-warning ms-auto">Draft</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="text-secondary small mb-2">
                            {{ $p->gelombang?->nama }} &middot;
                            {{ $p->tanggal_pengumuman->translatedFormat('d M Y') }} &middot;
                            Oleh: {{ $p->creator?->name ?? '-' }}
                        </div>
                        @if ($p->deskripsi)
                            <p class="mb-0">{{ Str::limit($p->deskripsi, 150) }}</p>
                        @endif
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <a href="{{ route('ppdb.pengumuman.show', $p) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        @if (! $p->published_at)
                            <form action="{{ route('ppdb.pengumuman.publish', $p) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">Publikasikan</button>
                            </form>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="salinLink('{{ route('ppdb.pengumuman.public', $p) }}', this)">Salin Link</button>
                        @endif
                        <form action="{{ route('ppdb.pengumuman.destroy', $p) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-secondary">Belum ada pengumuman.</div></div>
            </div>
        @endforelse
    </div>

    <div class="modal fade" id="modal-pengumuman" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('ppdb.pengumuman.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Pengumuman</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Gelombang</label>
                            <select name="gelombang_id" class="form-select" required>
                                <option value="">Pilih Gelombang</option>
                                @foreach ($gelombangs as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Judul Pengumuman</label>
                            <input type="text" name="judul" class="form-control" placeholder="Misal: Pengumuman Hasil Seleksi Gelombang 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Tanggal Pengumuman</label>
                            <input type="date" name="tanggal_pengumuman" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
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
