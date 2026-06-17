<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
                <h2 class="page-title mt-1">{{ $kegiatan->nama }}</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('kegiatan.kegiatan.edit', $kegiatan) }}" class="btn btn-outline-primary">
                    <i class="ti ti-edit"></i> Edit
                </a>
                <form action="{{ route('kegiatan.kegiatan.destroy', $kegiatan) }}" method="POST" onsubmit="return confirm('Hapus kegiatan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="ti ti-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Detail Kegiatan</h3></div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Pembina</div>
                            <div class="datagrid-content">{{ $kegiatan->pembina?->name ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tempat</div>
                            <div class="datagrid-content">{{ $kegiatan->tempat ?: '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Kuota</div>
                            <div class="datagrid-content">{{ $kegiatan->kuota ?: 'Tak terbatas' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">
                                <span class="badge {{ $kegiatan->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">{{ $kegiatan->status }}</span>
                            </div>
                        </div>
                    </div>
                    @if ($kegiatan->deskripsi)
                        <div class="mt-3">
                            <div class="fw-semibold mb-1">Deskripsi</div>
                            <p class="text-secondary mb-0">{{ $kegiatan->deskripsi }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($kegiatan->jadwal)
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Jadwal</h3></div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr><th>Hari</th><th>Jam Mulai</th><th>Jam Selesai</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($kegiatan->jadwal as $j)
                                    <tr>
                                        <td class="fw-semibold">{{ ucfirst($j['hari'] ?? '') }}</td>
                                        <td>{{ $j['jam_mulai'] ?? '-' }}</td>
                                        <td>{{ $j['jam_selesai'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($kegiatan->pertemuans->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Pertemuan Terbaru</h3>
                        <a href="{{ route('kegiatan.pertemuan.index', ['kegiatan_id' => $kegiatan->id]) }}" class="btn btn-outline-secondary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr><th>Tanggal</th><th>Jam</th><th>Materi</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($kegiatan->pertemuans as $p)
                                    <tr>
                                        <td>{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                                        <td>{{ $p->jam_mulai ? $p->jam_mulai . ' - ' . ($p->jam_selesai ?? 'selesai') : '-' }}</td>
                                        <td>{{ $p->materi ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">Pendaftar</h3>
                    <span class="badge bg-primary ms-auto">{{ $kegiatan->pendaftarans->count() }}</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($kegiatan->pendaftarans as $pd)
                        <div class="list-group-item d-flex align-items-center">
                            <span class="flex-grow-1">{{ $pd->santri?->full_name ?? '-' }}</span>
                            <span class="badge {{ $pd->status === 'terkonfirmasi' ? 'bg-success' : ($pd->status === 'dibatalkan' ? 'bg-danger' : 'bg-warning') }}">
                                {{ $pd->status }}
                            </span>
                        </div>
                    @empty
                        <div class="list-group-item text-secondary">Belum ada pendaftar.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
