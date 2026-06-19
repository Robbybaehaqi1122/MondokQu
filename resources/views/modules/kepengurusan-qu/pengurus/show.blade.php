<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">{{ $pengurus->nama }}</h2>
                <div class="text-secondary small">Detail data pengurus pondok.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('kepengurusan.pengurus.edit', $pengurus) }}" class="btn btn-outline-secondary">Edit</a>
                <a href="{{ route('kepengurusan.pengurus.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl mb-3" style="background: var(--tblr-primary); color: #fff; font-size: 2rem;">
                        {{ strtoupper(substr($pengurus->nama, 0, 1)) }}
                    </div>
                    <h3 class="mb-1">{{ $pengurus->nama }}</h3>
                    <div class="text-secondary">{{ $pengurus->jabatan ?: '-' }}</div>
                    <div class="mt-2">
                        <span class="badge {{ $pengurus->status ? 'bg-success' : 'bg-danger' }}">
                            {{ $pengurus->status ? 'Aktif' : 'Nonaktif' }}
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
                        <tr><td class="text-secondary" style="width:180px">Nama</td><td>{{ $pengurus->nama }}</td></tr>
                        <tr><td class="text-secondary">Jabatan</td><td>{{ $pengurus->jabatan ?: '-' }}</td></tr>
                        <tr><td class="text-secondary">No. Telp</td><td>{{ $pengurus->no_telp ?: '-' }}</td></tr>
                        <tr><td class="text-secondary">Alamat</td><td>{{ $pengurus->alamat ?: '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
