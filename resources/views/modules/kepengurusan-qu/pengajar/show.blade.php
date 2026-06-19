<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">{{ $pengajar->nama }}</h2>
                <div class="text-secondary small">Detail data pengajar.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('kepengurusan.pengajar.edit', $pengajar) }}" class="btn btn-outline-secondary">Edit</a>
                <a href="{{ route('kepengurusan.pengajar.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl mb-3" style="background: var(--tblr-primary); color: #fff; font-size: 2rem;">
                        {{ strtoupper(substr($pengajar->nama, 0, 1)) }}
                    </div>
                    <h3 class="mb-1">{{ $pengajar->nama }}</h3>
                    <div class="text-secondary">{{ $pengajar->bidang_keahlian ?: '-' }}</div>
                    <div class="mt-2">
                        <span class="badge {{ $pengajar->status ? 'bg-success' : 'bg-danger' }}">
                            {{ $pengajar->status ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Informasi Lengkap</h3></div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr><td class="text-secondary" style="width:180px">NIP</td><td>{{ $pengajar->nip ?: '-' }}</td></tr>
                        <tr><td class="text-secondary">Jenis Kelamin</td><td>{{ $pengajar->jenis_kelamin === 'L' ? 'Laki-laki' : ($pengajar->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td></tr>
                        <tr><td class="text-secondary">Tempat, Tanggal Lahir</td><td>{{ $pengajar->tempat_lahir ?: '-' }}{{ $pengajar->tanggal_lahir ? ', ' . $pengajar->tanggal_lahir->translatedFormat('d M Y') : '' }}</td></tr>
                        <tr><td class="text-secondary">Pendidikan</td><td>{{ $pengajar->pendidikan ?: '-' }}</td></tr>
                        <tr><td class="text-secondary">Bidang Keahlian</td><td>{{ $pengajar->bidang_keahlian ?: '-' }}</td></tr>
                        <tr><td class="text-secondary">No. Telp</td><td>{{ $pengajar->no_telp ?: '-' }}</td></tr>
                        <tr><td class="text-secondary">Alamat</td><td>{{ $pengajar->alamat ?: '-' }}</td></tr>
                    </table>
                </div>
            </div>

            @if ($pengajar->jadwals->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title">Jadwal Mengajar</h3></div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead><tr><th>Kegiatan</th><th>Hari</th><th>Jam</th><th>Tempat</th></tr></thead>
                            <tbody>
                                @foreach ($pengajar->jadwals as $jadwal)
                                    <tr>
                                        <td>{{ $jadwal->kegiatan }}</td>
                                        <td>{{ $jadwal->hari }}</td>
                                        <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}{{ $jadwal->jam_selesai ? ' - ' . \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') : '' }}</td>
                                        <td>{{ $jadwal->tempat ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
