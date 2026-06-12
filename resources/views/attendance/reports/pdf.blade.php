<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi {{ $monthName }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #555; margin-bottom: 20px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: center; }
        th { background: #f4f4f4; font-weight: 600; }
        td.left { text-align: left; }
        .pct-high { color: #2fb344; font-weight: bold; }
        .pct-mid { color: #ddaa22; font-weight: bold; }
        .pct-low { color: #d63939; font-weight: bold; }
        .footer { margin-top: 24px; font-size: 10px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <h2>Rekap Absensi Bulanan</h2>
    <div class="subtitle">{{ $monthName }} &mdash; {{ $roomName }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th class="left">Nama Santri</th>
                <th>NIS</th>
                <th>Kamar</th>
                <th>Hadir</th>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Alpa</th>
                <th>Telat</th>
                <th>Total</th>
                <th>% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rekap as $item)
                @php
                    $pctClass = $item['percentage'] >= 90 ? 'pct-high' : ($item['percentage'] >= 75 ? 'pct-mid' : 'pct-low');
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="left">{{ $item['full_name'] }}</td>
                    <td>{{ $item['nis'] ?: '-' }}</td>
                    <td>{{ $item['room_name'] }}</td>
                    <td>{{ number_format($item['present']) }}</td>
                    <td>{{ number_format($item['sick']) }}</td>
                    <td>{{ number_format($item['permission']) }}</td>
                    <td>{{ number_format($item['absent']) }}</td>
                    <td>{{ number_format($item['late']) }}</td>
                    <td>{{ number_format($item['total']) }}</td>
                    <td class="{{ $pctClass }}">{{ $item['percentage'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
</body>
</html>