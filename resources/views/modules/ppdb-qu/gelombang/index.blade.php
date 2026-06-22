<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
                <h2 class="page-title mt-1">Gelombang Pendaftaran</h2>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-gelombang">
                <i class="ti ti-plus"></i> Tambah Gelombang
            </button>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2" role="alert">
            <i class="ti ti-circle-check fs-3"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2" role="alert">
            <i class="ti ti-alert-circle fs-3"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Periode</th>
                        <th>Kuota</th>
                        <th>Biaya</th>
                        <th>Pendaftar</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gelombangs as $g)
                        <tr>
                            <td class="fw-semibold">{{ $g->nama }}</td>
                            <td>{{ $g->tanggal_mulai->translatedFormat('d M Y') }} - {{ $g->tanggal_selesai->translatedFormat('d M Y') }}</td>
                            <td>{{ $g->kuota ?: 'Tak terbatas' }}</td>
                            <td>Rp {{ number_format($g->biaya_pendaftaran, 0) }}</td>
                            <td><span class="badge bg-primary">{{ number_format($g->pendaftarans_count) }}</span></td>
                            <td>
                                <span class="badge {{ $g->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $g->status }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    @if ($g->status === 'aktif')
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="salinLink('{{ route('ppdb.daftar', $g) }}', this)">Salin Link</button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Actions</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('ppdb.gelombang.edit', $g) }}">Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('ppdb.gelombang.destroy', $g) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Hapus gelombang {{ $g->nama }}?')">Hapus</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-secondary">Belum ada gelombang pendaftaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($gelombangs->hasPages())
            <div class="card-footer">{{ $gelombangs->links() }}</div>
        @endif
    </div>

    <div class="modal fade" id="modal-gelombang" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('ppdb.gelombang.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Gelombang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Gelombang</label>
                            <input type="text" name="nama" class="form-control" placeholder="Misal: Gelombang 1" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" value="{{ now()->addMonth()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kuota Pendaftar</label>
                                <input type="number" name="kuota" class="form-control" min="0" placeholder="Kosongkan jika tak terbatas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Biaya Pendaftaran (Rp)</label>
                                <input type="number" name="biaya_pendaftaran" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="selesai">Selesai</option>
                            </select>
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
