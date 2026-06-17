<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary text-uppercase small fw-bold">KegiatanQu</div>
                <h2 class="page-title mt-1">Presensi Pertemuan</h2>
                <div class="text-secondary small">
                    {{ $pertemuan->kegiatan?->nama ?? '-' }} &middot;
                    {{ $pertemuan->tanggal->translatedFormat('l, d M Y') }}
                </div>
            </div>
            <a href="{{ route('kegiatan.pertemuan.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Hadir</h3>
            <div class="text-secondary small ms-3">
                @php
                    $totalHadir = $pertemuan->presensis->where('status', 'hadir')->count();
                    $totalSakit = $pertemuan->presensis->where('status', 'sakit')->count();
                    $totalIzin = $pertemuan->presensis->where('status', 'izin')->count();
                    $totalAlpha = $pertemuan->presensis->where('status', 'alpha')->count();
                @endphp
                Hadir: {{ $totalHadir }} | Sakit: {{ $totalSakit }} | Izin: {{ $totalIzin }} | Alpha: {{ $totalAlpha }}
            </div>
        </div>
        <form action="{{ route('kegiatan.presensi.store', $pertemuan) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="presensi-body">
                        @forelse ($pertemuan->presensis as $pr)
                            <tr>
                                <td class="fw-semibold">{{ $pr->santri?->full_name ?? '-' }}</td>
                                <td>
                                    <select name="presensi[{{ $pr->id }}][status]" class="form-select form-select-sm" style="width:auto;">
                                        <option value="hadir" @selected($pr->status === 'hadir')>Hadir</option>
                                        <option value="sakit" @selected($pr->status === 'sakit')>Sakit</option>
                                        <option value="izin" @selected($pr->status === 'izin')>Izin</option>
                                        <option value="alpha" @selected($pr->status === 'alpha')>Alpha</option>
                                    </select>
                                    <input type="hidden" name="presensi[{{ $pr->id }}][santri_id]" value="{{ $pr->santri_id }}">
                                </td>
                                <td><input type="text" name="presensi[{{ $pr->id }}][catatan]" class="form-control form-control-sm" value="{{ $pr->catatan }}" placeholder="Catatan"></td>
                            </tr>
                        @empty
                            @php
                                $pendaftarTerkonfirmasi = $pertemuan->kegiatan?->pendaftarans()
                                    ->where('status', 'terkonfirmasi')
                                    ->with('santri')
                                    ->get();
                            @endphp
                            @forelse ($pendaftarTerkonfirmasi as $pd)
                                <tr>
                                    <td class="fw-semibold">{{ $pd->santri?->full_name ?? '-' }}</td>
                                    <td>
                                        <select name="presensi[{{ $pd->santri_id }}][status]" class="form-select form-select-sm" style="width:auto;">
                                            <option value="hadir">Hadir</option>
                                            <option value="sakit">Sakit</option>
                                            <option value="izin">Izin</option>
                                            <option value="alpha">Alpha</option>
                                        </select>
                                        <input type="hidden" name="presensi[{{ $pd->santri_id }}][santri_id]" value="{{ $pd->santri_id }}">
                                    </td>
                                    <td><input type="text" name="presensi[{{ $pd->santri_id }}][catatan]" class="form-control form-control-sm" placeholder="Catatan"></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-secondary">Tidak ada peserta terkonfirmasi untuk kegiatan ini.</td></tr>
                            @endforelse
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pertemuan->presensis->isNotEmpty() || ($pendaftarTerkonfirmasi ?? collect())->isNotEmpty())
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Simpan Presensi</button>
                </div>
            @endif
        </form>
    </div>
</x-app-layout>
