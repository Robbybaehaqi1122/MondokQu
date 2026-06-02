<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor - {{ $santri->full_name }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1a1a1a; }

        .letterhead { text-align: center; border-bottom: 2px solid #1a56db; padding-bottom: 10px; margin-bottom: 20px; }
        .letterhead .institution { font-size: 16pt; font-weight: bold; color: #1a56db; }
        .letterhead .subtitle { font-size: 9pt; color: #555; margin-top: 3px; }

        .title { text-align: center; font-size: 14pt; font-weight: bold; margin: 20px 0; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 4px 8px; }
        .info-table .label { font-weight: bold; width: 120px; }

        table.nilai { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.nilai th, table.nilai td { border: 1px solid #ccc; padding: 6px 8px; text-align: center; font-size: 9pt; }
        table.nilai th { background: #f0f4ff; font-weight: bold; }
        table.nilai .tuntas { color: #155724; }
        table.nilai .tidak-tuntas { color: #721c24; }

        .ringkasan { margin-top: 20px; }
        .ringkasan h3 { font-size: 11pt; border-bottom: 1px solid #ddd; padding-bottom: 4px; }

        .footer { text-align: center; margin-top: 40px; font-size: 9pt; color: #888; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="institution">PONDOK PESANTREN {{ config('app.ponpes_name', 'Mondok Qu') }}</div>
        <div class="subtitle">{{ config('app.ponpes_address', 'Jl. Pendidikan No. 1, Kota Santri') }}</div>
        <div class="subtitle">Telp: {{ config('app.ponpes_phone', '(021) 1234-5678') }} &middot; Email: {{ config('app.ponpes_email', 'info@mondok-qu.sch.id') }}</div>
    </div>

    <div class="title">LAPORAN HASIL BELAJAR SANTRI</div>
    <div style="text-align: center; font-size: 9pt; margin-bottom: 20px;">Semester {{ $semester }}</div>

    <table class="info-table">
        <tr><td class="label">Nama Santri</td><td>: {{ $santri->full_name }}</td></tr>
        <tr><td class="label">NIS</td><td>: {{ $santri->nis }}</td></tr>
        <tr><td class="label">Kamar</td><td>: {{ $santri->displayRoomName() }}</td></tr>
        <tr><td class="label">Wali Santri</td><td>: {{ $santri->displayGuardianName() ?: '-' }}</td></tr>
    </table>

    <h3 style="font-size: 11pt; border-bottom: 1px solid #ddd; padding-bottom: 4px;">A. Nilai Akademik</h3>

    <table class="nilai">
        <thead>
            <tr>
                <th>No</th>
                <th style="text-align:left">Mata Pelajaran</th>
                <th>KKM</th>
                <th>Pengetahuan</th>
                <th>Keterampilan</th>
                <th>Rata-rata</th>
                <th>Rata Kelas</th>
                <th>Predikat</th>
                <th>Ket.</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($nilais as $i => $nilai)
                @php
                    $kkm = $nilai->mataPelajaran?->kkm ?? 70;
                    $na = $nilai->nilai_akhir;
                    $rk = (int) ($rataRataKelas[$nilai->mata_pelajaran_id] ?? 0);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="text-align:left">{{ $nilai->mataPelajaran?->nama ?? '-' }}</td>
                    <td>{{ $kkm }}</td>
                    <td>{{ $nilai->nilai_pengetahuan }}</td>
                    <td>{{ $nilai->nilai_keterampilan }}</td>
                    <td><strong>{{ $na }}</strong></td>
                    <td>{{ $rk }}</td>
                    <td>{{ $nilai->predikat }}</td>
                    <td class="{{ $na >= $kkm ? 'tuntas' : 'tidak-tuntas' }}">
                        {{ $na >= $kkm ? 'Tuntas' : 'TT' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center">Belum ada nilai akademik.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="ringkasan">
        <h3>B. Ringkasan Tahfidz</h3>
        <table class="nilai">
            <tr>
                <td style="text-align:left"><strong>Total Ayat</strong></td>
                <td>{{ number_format((int) ($tahfidzStats?->total_ayat ?? 0)) }} ayat</td>
            </tr>
            <tr>
                <td style="text-align:left"><strong>Total Sesi Setoran</strong></td>
                <td>{{ number_format((int) ($tahfidzStats?->total_record ?? 0)) }} sesi</td>
            </tr>
        </table>
    </div>

    <div class="ringkasan">
        <h3>C. Ringkasan Pelanggaran</h3>
        <table class="nilai">
            <tr>
                <td style="text-align:left"><strong>Total Poin Pelanggaran</strong></td>
                <td>{{ number_format($totalPoinPelanggaran) }} poin</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dicetak pada {{ now()->translatedFormat('d F Y H:i') }} &middot; {{ config('app.ponpes_name', 'Mondok Qu') }}
    </div>
</body>
</html>
