<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Detail Nilai Sikap</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }} &middot; Semester {{ $semester }}</div>
            </div>
            <div class="d-flex gap-2">
                @if ($nilaiSikap)
                    <a href="{{ route('akademik.nilai-sikap.edit', $nilaiSikap) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('akademik.nilai-sikap.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @if ($nilaiSikap)
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="text-primary">Sikap Spiritual (Akhlak kepada Allah)</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-secondary small">Predikat</label>
                            <div>
                                @php
                                    $predikatBadges = ['SB' => 'bg-success-lt text-success', 'B' => 'bg-primary-lt text-primary', 'C' => 'bg-warning-lt text-warning', 'K' => 'bg-danger-lt text-danger'];
                                @endphp
                                @if ($nilaiSikap->sikap_spiritual)
                                    <span class="badge {{ $predikatBadges[$nilaiSikap->sikap_spiritual] ?? '' }} fs-6">
                                        {{ $nilaiSikap->sikap_spiritual }} - {{ $nilaiSikap->sikapSpiritualLabel() }}
                                    </span>
                                @else
                                    <span class="text-secondary">Belum dinilai</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="text-secondary small">Deskripsi / Uraian</label>
                            <div class="mt-1">{{ $nilaiSikap->deskripsi_spiritual ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="text-success">Sikap Sosial (Akhlak kepada Sesama)</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-secondary small">Predikat</label>
                            <div>
                                @if ($nilaiSikap->sikap_sosial)
                                    <span class="badge {{ $predikatBadges[$nilaiSikap->sikap_sosial] ?? '' }} fs-6">
                                        {{ $nilaiSikap->sikap_sosial }} - {{ $nilaiSikap->sikapSosialLabel() }}
                                    </span>
                                @else
                                    <span class="text-secondary">Belum dinilai</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="text-secondary small">Deskripsi / Uraian</label>
                            <div class="mt-1">{{ $nilaiSikap->deskripsi_sosial ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Catatan Wali</h3>
                    </div>
                    <div class="card-body">
                        {{ $nilaiSikap->catatan_wali ?? '-' }}
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-secondary">Santri</dt>
                            <dd class="col-sm-7">{{ $santri->full_name }}</dd>
                            <dt class="col-sm-5 text-secondary">NIS</dt>
                            <dd class="col-sm-7">{{ $santri->nis }}</dd>
                            <dt class="col-sm-5 text-secondary">Semester</dt>
                            <dd class="col-sm-7">{{ $semester }}</dd>
                            <dt class="col-sm-5 text-secondary">Terakhir Diubah</dt>
                            <dd class="col-sm-7">{{ $nilaiSikap->updated_at->diffForHumans() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="text-secondary mb-3">
                    <i class="ti ti-file-off fs-1"></i>
                </div>
                <h4>Belum Ada Data</h4>
                <p class="text-secondary">Nilai sikap untuk santri ini pada semester {{ $semester }} belum dicatat.</p>
                <a href="{{ route('akademik.nilai-sikap.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Input Nilai Sikap
                </a>
            </div>
        </div>
    @endif
</x-app-layout>
