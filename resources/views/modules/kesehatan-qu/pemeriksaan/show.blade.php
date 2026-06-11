<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Detail Pemeriksaan</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('kesehatan.pemeriksaan.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            <form method="POST" action="{{ route('kesehatan.pemeriksaan.destroy', $pemeriksaan) }}" onsubmit="return confirm('Hapus pemeriksaan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
            </form>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Pemeriksaan</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Tanggal</dt>
                        <dd class="col-sm-9">{{ $pemeriksaan->tanggal_pemeriksaan->translatedFormat('d F Y') }}</dd>
                        <dt class="col-sm-3">Santri</dt>
                        <dd class="col-sm-9">{{ $pemeriksaan->santri?->full_name ?? 'Unknown' }}</dd>
                        <dt class="col-sm-3">NIS</dt>
                        <dd class="col-sm-9">{{ $pemeriksaan->santri?->nis ?? '-' }}</dd>
                        <dt class="col-sm-3">Keluhan</dt>
                        <dd class="col-sm-9">{{ $pemeriksaan->keluhan }}</dd>
                        @if ($pemeriksaan->diagnosis)
                            <dt class="col-sm-3">Diagnosis</dt>
                            <dd class="col-sm-9">{{ $pemeriksaan->diagnosis }}</dd>
                        @endif
                        @if ($pemeriksaan->tindakan)
                            <dt class="col-sm-3">Tindakan</dt>
                            <dd class="col-sm-9">{{ $pemeriksaan->tindakan }}</dd>
                        @endif
                        @if ($pemeriksaan->catatan)
                            <dt class="col-sm-3">Catatan</dt>
                            <dd class="col-sm-9">{{ $pemeriksaan->catatan }}</dd>
                        @endif
                        <dt class="col-sm-3">Dicatat Oleh</dt>
                        <dd class="col-sm-9">{{ $pemeriksaan->pencatat?->name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>

            @if ($pemeriksaan->pemakaianObat->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Obat yang Diberikan</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Obat</th>
                                    <th>Jumlah</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pemeriksaan->pemakaianObat as $po)
                                    <tr>
                                        <td>{{ $po->obat?->nama_obat ?? 'Unknown' }}</td>
                                        <td>{{ $po->jumlah }} {{ $po->obat?->satuan ?? 'pcs' }}</td>
                                        <td>{{ $po->catatan ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            @if ($pemeriksaan->rujukan)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Rujukan</h3>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Tempat Rujukan</dt>
                            <dd class="mb-2">{{ $pemeriksaan->rujukan->tempat_rujukan }}</dd>
                            @if ($pemeriksaan->rujukan->diagnosis_dokter)
                                <dt>Diagnosis Dokter</dt>
                                <dd class="mb-2">{{ $pemeriksaan->rujukan->diagnosis_dokter }}</dd>
                            @endif
                            <dt>Tanggal Rujuk</dt>
                            <dd class="mb-2">{{ $pemeriksaan->rujukan->tanggal_rujuk->translatedFormat('d M Y') }}</dd>
                            @if ($pemeriksaan->rujukan->tanggal_kembali)
                                <dt>Tanggal Kembali</dt>
                                <dd class="mb-2">{{ $pemeriksaan->rujukan->tanggal_kembali->translatedFormat('d M Y') }}</dd>
                            @endif
                            @if ($pemeriksaan->rujukan->catatan)
                                <dt>Catatan</dt>
                                <dd class="mb-0">{{ $pemeriksaan->rujukan->catatan }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
