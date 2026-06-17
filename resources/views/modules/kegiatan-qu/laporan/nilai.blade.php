<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
            <h2 class="page-title mt-1">Laporan Nilai</h2>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <select name="kegiatan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih Kegiatan</option>
                        @foreach ($kegiatans as $k)
                            <option value="{{ $k->id }}" @selected($selectedKegiatan?->id === $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedKegiatan && $nilaisByKegiatan->isNotEmpty())
        @foreach ($nilaisByKegiatan as $kegiatanNama => $nilaiGroup)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $kegiatanNama }}</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Aspek</th>
                                <th>Nilai</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nilaiGroup as $n)
                                <tr>
                                    <td class="fw-semibold">{{ $n->santri?->full_name ?? '-' }}</td>
                                    <td><span class="badge bg-info">{{ $n->aspek }}</span></td>
                                    <td>{{ $n->nilai ?? '-' }}</td>
                                    <td>{{ $n->catatan ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @elseif ($selectedKegiatan)
        <div class="card">
            <div class="card-body text-secondary">Belum ada data nilai untuk kegiatan ini.</div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-secondary">Pilih kegiatan untuk melihat laporan nilai.</div>
        </div>
    @endif
</x-app-layout>
