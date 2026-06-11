<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-secondary text-uppercase small fw-bold">KesehatanQu</div>
            <h2 class="page-title mt-1">Rekam Medis Santri</h2>
        </div>
    </x-slot>

    <div class="row mb-3">
        <div class="col-lg-6">
            <form method="GET" action="{{ route('kesehatan.rekam-medis.index') }}">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama atau NIS santri..." value="{{ $filters['q'] }}">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    @if ($filters['q'])
                        <a href="{{ route('kesehatan.rekam-medis.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Santri</th>
                        <th>Gol. Darah</th>
                        <th>Riwayat Penyakit</th>
                        <th>Alergi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($santris as $santri)
                        <tr>
                            <td>{{ $santri->nis }}</td>
                            <td class="fw-semibold">{{ $santri->full_name }}</td>
                            <td>
                                @if ($santri->rekamMedis)
                                    <span class="badge bg-cyan-lt">{{ $santri->rekamMedis->golongan_darah ?: '-' }}</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($santri->rekamMedis?->riwayat_penyakit)
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;">{{ $santri->rekamMedis->riwayat_penyakit }}</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $alergi = collect();
                                    if ($santri->rekamMedis?->alergi_obat) $alergi->push('Obat: '.$santri->rekamMedis->alergi_obat);
                                    if ($santri->rekamMedis?->alergi_makanan) $alergi->push('Makanan: '.$santri->rekamMedis->alergi_makanan);
                                @endphp
                                @if ($alergi->isNotEmpty())
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;">{{ $alergi->implode('; ') }}</span>
                                @else
                                    <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('kesehatan.rekam-medis.show', $santri) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada data santri.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($santris->hasPages())
            <div class="card-footer">
                {{ $santris->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
