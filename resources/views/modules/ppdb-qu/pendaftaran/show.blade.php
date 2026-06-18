<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">PpdbQu</div>
                <h2 class="page-title mt-1">{{ $pendaftaran->nama_lengkap }}</h2>
                <div class="text-secondary small"><code>{{ $pendaftaran->nomor_pendaftaran }}</code></div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('ppdb.cetak.formulir', $pendaftaran) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-file-text"></i> Formulir
                </a>
                <a href="{{ route('ppdb.cetak.kartu', $pendaftaran) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-id"></i> Kartu
                </a>
                @if ($pendaftaran->status === 'diterima' || $pendaftaran->status === 'daftar_ulang')
                    <a href="{{ route('ppdb.cetak.surat-terima', $pendaftaran) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-certificate"></i> Surat Terima
                    </a>
                @endif
                <form action="{{ route('ppdb.pendaftaran.destroy', $pendaftaran) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Hapus pendaftaran?')">
                        <i class="ti ti-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Data Calon Santri</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nama Lengkap</div>
                            <div class="datagrid-content">{{ $pendaftaran->nama_lengkap }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tempat / Tgl Lahir</div>
                            <div class="datagrid-content">{{ $pendaftaran->tempat_lahir ?: '-' }} / {{ $pendaftaran->tanggal_lahir?->translatedFormat('d M Y') ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Jenis Kelamin</div>
                            <div class="datagrid-content">{{ $pendaftaran->jenis_kelamin }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">No. HP</div>
                            <div class="datagrid-content">{{ $pendaftaran->no_hp }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Email</div>
                            <div class="datagrid-content">{{ $pendaftaran->email ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Asal Sekolah</div>
                            <div class="datagrid-content">{{ $pendaftaran->asal_sekolah ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Alamat</div>
                            <div class="datagrid-content">{{ $pendaftaran->alamat ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Data Orang Tua</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nama Ayah</div>
                            <div class="datagrid-content">{{ $pendaftaran->nama_ayah ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nama Ibu</div>
                            <div class="datagrid-content">{{ $pendaftaran->nama_ibu ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">No. HP Orang Tua</div>
                            <div class="datagrid-content">{{ $pendaftaran->no_hp_orangtua ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($pendaftaran->berkas)
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Berkas</h3></div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach ($pendaftaran->berkas as $path)
                                <li><a href="{{ Storage::url($path) }}" target="_blank" class="text-reset"><i class="ti ti-file"></i> {{ basename($path) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Seleksi</h3>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSeleksi">Tambah Seleksi</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Nilai</th>
                                <th>Hasil</th>
                                <th>Penguji</th>
                                <th>Tanggal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendaftaran->seleksis as $s)
                                <tr>
                                    <td><span class="badge bg-info">{{ $s->jenis }}</span></td>
                                    <td>{{ $s->nilai ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $s->hasil === 'lulus' ? 'bg-success' : ($s->hasil === 'tidak_lulus' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ $s->hasil }}
                                        </span>
                                    </td>
                                    <td>{{ $s->penguji?->name ?? '-' }}</td>
                                    <td>{{ $s->tanggal_seleksi?->translatedFormat('d M Y') ?: '-' }}</td>
                                    <td>
                                        <form action="{{ route('ppdb.seleksi.destroy', $s) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-secondary">Belum ada data seleksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Status & Aksi</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status Saat Ini</label>
                        <div>
                            <span class="badge fs-5 {{ $pendaftaran->status === 'diterima' || $pendaftaran->status === 'daftar_ulang' ? 'bg-success' : ($pendaftaran->status === 'ditolak' ? 'bg-danger' : 'bg-warning') }}">
                                {{ $pendaftaran->status }}
                            </span>
                        </div>
                    </div>

                    <form action="{{ route('ppdb.pendaftaran.update', $pendaftaran) }}" method="POST" class="mb-3">
                        @csrf @method('PUT')
                        <div class="mb-2">
                            <label class="form-label">Ubah Status</label>
                            <select name="status" class="form-select">
                                <option value="menunggu" @selected($pendaftaran->status === 'menunggu')>Menunggu</option>
                                <option value="diproses" @selected($pendaftaran->status === 'diproses')>Diproses</option>
                                <option value="diterima" @selected($pendaftaran->status === 'diterima')>Diterima</option>
                                <option value="ditolak" @selected($pendaftaran->status === 'ditolak')>Ditolak</option>
                                <option value="daftar_ulang" @selected($pendaftaran->status === 'daftar_ulang')>Daftar Ulang</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2">{{ $pendaftaran->catatan }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Status</button>
                    </form>

                    @if ($pendaftaran->status === 'diterima')
                        <form action="{{ route('ppdb.pendaftaran.daftar-ulang', $pendaftaran) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Konfirmasi daftar ulang? Santri baru akan dibuat.')">
                                <i class="ti ti-user-check"></i> Konfirmasi Daftar Ulang
                            </button>
                        </form>
                    @endif

                    @if ($pendaftaran->catatan)
                        <div class="mt-3">
                            <div class="fw-semibold mb-1">Catatan Internal</div>
                            <p class="text-secondary mb-0">{{ $pendaftaran->catatan }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSeleksi" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('ppdb.seleksi.store', $pendaftaran) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Seleksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Jenis Seleksi</label>
                        <select name="jenis" class="form-select" required>
                            <option value="">Pilih Jenis</option>
                            <option value="administrasi">Administrasi</option>
                            <option value="tes_baca_quran">Tes Baca Al-Quran</option>
                            <option value="wawancara">Wawancara</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai (0-100)</label>
                        <input type="number" name="nilai" class="form-control" min="0" max="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Hasil</label>
                        <select name="hasil" class="form-select" required>
                            <option value="lulus">Lulus</option>
                            <option value="tidak_lulus">Tidak Lulus</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Seleksi</label>
                        <input type="date" name="tanggal_seleksi" class="form-control" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
