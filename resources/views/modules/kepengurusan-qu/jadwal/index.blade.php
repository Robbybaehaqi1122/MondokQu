<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">Jadwal Ngaji & Pengajar</h2>
                <div class="text-secondary small">Jadwal kegiatan ngaji dan jadwal mengajar pengajar.</div>
            </div>
            <a href="{{ route('kepengurusan.jadwal.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Jadwal
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari kegiatan atau tempat..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="hari" class="form-select">
                        <option value="">Semua Hari</option>
                        @foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h)
                            <option value="{{ $h }}" @selected(request('hari') === $h)>{{ $h }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="pengajar_id" class="form-select">
                        <option value="">Semua Pengajar</option>
                        @foreach ($pengajars as $p)
                            <option value="{{ $p->id }}" @selected(request('pengajar_id') == $p->id)>{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if (request()->anyFilled(['search', 'hari', 'pengajar_id']))
                    <div class="col-12">
                        <a href="{{ route('kepengurusan.jadwal.index') }}" class="btn btn-ghost-secondary">Reset</a>
                    </div>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Pengajar</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Tempat</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwals as $jadwal)
                        <tr>
                            <td class="fw-semibold">{{ $jadwal->kegiatan }}</td>
                            <td>{{ $jadwal->pengajar?->nama ?? '-' }}</td>
                            <td>{{ $jadwal->hari }}</td>
                            <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}{{ $jadwal->jam_selesai ? ' - ' . \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') : '' }}</td>
                            <td>{{ $jadwal->tempat ?: '-' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('kepengurusan.jadwal.edit', $jadwal) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('kepengurusan.jadwal.destroy', $jadwal) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-secondary">Belum ada jadwal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($jadwals->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $jadwals->links() }}</div>
        @endif
    </div>
</x-app-layout>
