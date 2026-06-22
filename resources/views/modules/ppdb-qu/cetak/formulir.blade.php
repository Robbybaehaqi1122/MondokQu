<html>
<head>
    <meta charset="utf-8">
    <title>Formulir PPDB - {{ $pendaftaran->nomor_pendaftaran }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0 0 5px; }
        .header h2 { font-size: 14px; margin: 0 0 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        td, th { padding: 6px 8px; border: 1px solid #000; }
        th { background: #f0f0f0; text-align: left; }
        .label { width: 35%; font-weight: bold; }
        .ttd { margin-top: 40px; }
        .ttd td { border: none; text-align: center; padding-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FORMULIR PENDAFTARAN</h1>
        <h2>PPDB Online - {{ $tenant?->settings['ponpes_name'] ?? $tenant?->name ?? 'Pesantren' }}</h2>
        <p>No. Pendaftaran: <strong>{{ $pendaftaran->nomor_pendaftaran }}</strong></p>
    </div>

    <table>
        <tr><td class="label">Gelombang</td><td>{{ $pendaftaran->gelombang?->nama ?? '-' }}</td></tr>
        <tr><td class="label">Nama Lengkap</td><td>{{ $pendaftaran->nama_lengkap }}</td></tr>
        <tr><td class="label">Tempat / Tgl Lahir</td><td>{{ $pendaftaran->tempat_lahir ?: '-' }} / {{ $pendaftaran->tanggal_lahir?->translatedFormat('d M Y') ?: '-' }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td>{{ $pendaftaran->jenis_kelamin }}</td></tr>
        <tr><td class="label">Alamat</td><td>{{ $pendaftaran->alamat ?: '-' }}</td></tr>
        <tr><td class="label">No. HP</td><td>{{ $pendaftaran->no_hp }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $pendaftaran->email ?: '-' }}</td></tr>
        <tr><td class="label">Asal Sekolah</td><td>{{ $pendaftaran->asal_sekolah ?: '-' }}</td></tr>
    </table>

    <table>
        <tr><th colspan="2">Data Orang Tua</th></tr>
        <tr><td class="label">Nama Ayah</td><td>{{ $pendaftaran->nama_ayah ?: '-' }}</td></tr>
        <tr><td class="label">Nama Ibu</td><td>{{ $pendaftaran->nama_ibu ?: '-' }}</td></tr>
        <tr><td class="label">No. HP Orang Tua</td><td>{{ $pendaftaran->no_hp_orangtua ?: '-' }}</td></tr>
    </table>

    <table class="ttd">
        <tr>
            <td>Pendaftar</td>
            <td>Mengetahui,<br>Orang Tua/Wali</td>
            <td>Petugas PPDB</td>
        </tr>
    </table>
</body>
</html>
