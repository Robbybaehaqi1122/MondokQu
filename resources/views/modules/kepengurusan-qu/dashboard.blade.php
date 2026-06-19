<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Kepengurusan</h2>
            <div class="text-secondary mt-1">Pantau data pengajar, pengurus, dan jadwal kegiatan pondok.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-4">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Pengajar</div>
                <div class="fs-2 fw-bold">{{ number_format($totalPengajar) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Pengurus</div>
                <div class="fs-2 fw-bold">{{ number_format($totalPengurus) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Jadwal</div>
                <div class="fs-2 fw-bold">{{ number_format($totalJadwal) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Jadwal Terbaru</h3></div>
        @if ($recentJadwals->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Kegiatan</th><th>Pengajar</th><th>Hari</th><th>Jam</th><th>Tempat</th></tr></thead>
                    <tbody>
                        @foreach ($recentJadwals as $jadwal)
                            <tr>
                                <td class="fw-semibold">{{ $jadwal->kegiatan }}</td>
                                <td>{{ $jadwal->pengajar?->nama ?? '-' }}</td>
                                <td>{{ $jadwal->hari }}</td>
                                <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}{{ $jadwal->jam_selesai ? ' - ' . \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') : '' }}</td>
                                <td>{{ $jadwal->tempat ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body"><div class="text-secondary">Belum ada jadwal.</div></div>
        @endif
    </div>
</x-app-layout>
