<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Peserta - {{ $pendaftaran->nomor_pendaftaran }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .card { width: 350px; margin: 0 auto; padding: 20px; border: 2px solid #000; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { font-size: 14px; margin: 0 0 3px; }
        .header h2 { font-size: 12px; margin: 0 0 5px; }
        .no-reg { text-align: center; font-size: 16px; font-weight: bold; margin: 10px 0; letter-spacing: 2px; }
        table { width: 100%; }
        td { padding: 4px 0; }
        .label { font-weight: bold; width: 40%; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>KARTU PESERTA</h1>
            <h2>PPDB {{ $tenant?->settings['ponpes_name'] ?? $tenant?->name ?? 'Pesantren' }}</h2>
        </div>
        <div class="no-reg">{{ $pendaftaran->nomor_pendaftaran }}</div>
        <table>
            <tr><td class="label">Nama</td><td>: {{ $pendaftaran->nama_lengkap }}</td></tr>
            <tr><td class="label">Jenis Kelamin</td><td>: {{ $pendaftaran->jenis_kelamin }}</td></tr>
            <tr><td class="label">Gelombang</td><td>: {{ $pendaftaran->gelombang?->nama ?? '-' }}</td></tr>
            <tr><td class="label">Tanggal Daftar</td><td>: {{ $pendaftaran->created_at->translatedFormat('d M Y') }}</td></tr>
        </table>
    </div>
</body>
</html>
