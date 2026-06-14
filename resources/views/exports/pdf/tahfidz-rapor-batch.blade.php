<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapor Tahfidz - {{ $room->name }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1a1a1a; }

        .letterhead { text-align: center; border-bottom: 2px solid #1a56db; padding-bottom: 10px; margin-bottom: 20px; }
        .letterhead .institution { font-size: 16pt; font-weight: bold; color: #1a56db; }
        .letterhead .subtitle { font-size: 9pt; color: #555; margin-top: 3px; }
        .letterhead .address { font-size: 8pt; color: #6b7280; margin-top: 2px; }

        .title { text-align: center; font-size: 14pt; font-weight: bold; margin: 20px 0; }
        .subtitle-text { text-align: center; font-size: 9pt; color: #6b7280; margin-bottom: 20px; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 3px 8px; font-size: 9pt; }
        .info-table .label { font-weight: bold; width: 130px; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 5px 6px; text-align: center; font-size: 8pt; }
        table.data th { background: #f0f4ff; font-weight: bold; }

        .ringkasan-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .ringkasan-table td { padding: 3px 10px; font-size: 9pt; border: none; }
        .ringkasan-table td:first-child { font-weight: bold; width: 180px; }
        .ringkasan-table .nilai-lancar { color: #155724; }
        .ringkasan-table .nilai-perlu-pengulangan { color: #856404; }
        .ringkasan-table .nilai-belum-lancar { color: #721c24; }

        .signature { margin-top: 35px; display: flex; justify-content: space-between; }
        .signature .signature-block { text-align: center; width: 45%; font-size: 9pt; }
        .signature .signature-block .sign-space { margin-bottom: 65px; }

        .page-break { page-break-before: always; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 7pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 4px; }

        .santri-header { background: #e8f0fe; font-weight: bold; font-size: 10pt; padding: 6px 8px; margin-top: 20px; margin-bottom: 8px; border-left: 4px solid #1a56db; }

        .badge-lancar { display: inline-block; background: #d4edda; color: #155724; padding: 1px 6px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
        .badge-perlu-pengulangan { display: inline-block; background: #fff3cd; color: #856404; padding: 1px 6px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
        .badge-belum-lancar { display: inline-block; background: #f8d7da; color: #721c24; padding: 1px 6px; border-radius: 3px; font-size: 7pt; font-weight: bold; }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="institution">PONDOK PESANTREN {{ config('app.ponpes_name', 'Mondok Qu') }}</div>
        <div class="subtitle">{{ config('app.ponpes_address', 'Jl. Pendidikan No. 1, Kota Santri') }}</div>
        <div class="address">Telp: {{ config('app.ponpes_phone', '(021) 1234-5678') }} &middot; Email: {{ config('app.ponpes_email', 'info@mondok-qu.sch.id') }}</div>
    </div>

    <div class="title">LAPORAN RAPOR HAFALAN AL-QUR'AN</div>
    <div class="subtitle-text">
        Kelas: <strong>{{ $room->name }}</strong> &mdash;
        Periode {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->translatedFormat('d F Y') : 'Awal' }}
        &mdash; {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
    </div>

    @foreach ($santriRapors as $index => $rapor)
        @if ($index > 0)
            <div class="page-break"></div>
        @endif

        <div class="santri-header">
            {{ $index + 1 }}. {{ $rapor['santri']->full_name }} (NIS {{ $rapor['santri']->nis }})
        </div>

        <table class="ringkasan-table">
            <tr>
                <td>Total Setoran</td>
                <td>: {{ number_format($rapor['total_sessions']) }} kali</td>
            </tr>
            <tr>
                <td>Total Ayat</td>
                <td>: {{ number_format($rapor['total_ayat']) }} ayat</td>
            </tr>
            <tr>
                <td>Ayat Lancar</td>
                <td class="nilai-lancar">: {{ number_format($rapor['total_lancar']) }} ayat
                    ({{ $rapor['total_ayat'] > 0 ? round(($rapor['total_lancar'] / $rapor['total_ayat']) * 100, 1) : 0 }}%)</td>
            </tr>
            <tr>
                <td>Ayat Perlu Pengulangan</td>
                <td class="nilai-perlu-pengulangan">: {{ number_format($rapor['total_perlu_pengulangan']) }} ayat
                    ({{ $rapor['total_ayat'] > 0 ? round(($rapor['total_perlu_pengulangan'] / $rapor['total_ayat']) * 100, 1) : 0 }}%)</td>
            </tr>
            <tr>
                <td>Ayat Belum Lancar</td>
                <td class="nilai-belum-lancar">: {{ number_format($rapor['total_belum_lancar']) }} ayat
                    ({{ $rapor['total_ayat'] > 0 ? round(($rapor['total_belum_lancar'] / $rapor['total_ayat']) * 100, 1) : 0 }}%)</td>
            </tr>
        </table>

        <table class="data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Surah</th>
                    <th>Ayat</th>
                    <th>Jml</th>
                    <th>Penilaian</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($rapor['sessions'] as $session)
                    @foreach ($session->records as $record)
                        @php
                            $ayatCount = ($record->verse_end - $record->verse_start) + 1;
                            $badgeClass = match ($record->evaluation) {
                                'lancar' => 'badge-lancar',
                                'perlu_pengulangan' => 'badge-perlu-pengulangan',
                                'belum_lancar' => 'badge-belum-lancar',
                                default => '',
                            };
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $session->session_date?->translatedFormat('d/m/Y') ?? '-' }}</td>
                            <td style="text-align:left">{{ $record->surah?->name ?? '-' }}</td>
                            <td>{{ $record->verseRangeLabel() }}</td>
                            <td>{{ number_format($ayatCount) }}</td>
                            <td><span class="{{ $badgeClass }}">{{ $record->evaluationLabel() }}</span></td>
                            <td style="text-align:left; font-size: 7pt;">{{ $record->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                @endforeach
                @if ($rapor['sessions']->isEmpty())
                    <tr><td colspan="7" style="color:#888;">Belum ada setoran hafalan.</td></tr>
                @endif
            </tbody>
        </table>
    @endforeach

    <div class="signature">
        <div class="signature-block">
            <div>{{ config('app.ponpes_city', 'Kota Santri') }}, {{ now()->translatedFormat('d F Y') }}</div>
            <div class="sign-space"></div>
            <div style="font-weight: bold;">Musyrif Tahfidz</div>
            <div style="margin-top: 3px; font-size: 8pt;">_________________________</div>
        </div>
        <div class="signature-block">
            <div>&nbsp;</div>
            <div class="sign-space"></div>
            <div style="font-weight: bold;">Kepala Pondok</div>
            <div style="margin-top: 3px; font-size: 8pt;">{{ config('app.ponpes_headmaster', '_________________________') }}</div>
        </div>
    </div>

    <div class="footer">
        Dokumen digenerate otomatis oleh {{ config('app.name', 'Mondok Qu') }} &mdash; {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>
</html>
