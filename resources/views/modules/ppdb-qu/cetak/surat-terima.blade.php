<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Diterima - {{ $pendaftaran->nomor_pendaftaran }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 16px; margin: 0 0 5px; }
        .header h2 { font-size: 14px; margin: 0 0 5px; }
        .content { margin-bottom: 30px; }
        .content p { line-height: 1.8; text-align: justify; }
        table { width: 100%; margin: 15px 0; }
        td { padding: 4px 0; }
        .label { width: 35%; font-weight: bold; }
        .ttd { margin-top: 50px; width: 100%; }
        .ttd td { border: none; text-align: center; padding-top: 60px; }
        .no-surat { text-align: center; font-style: italic; margin-bottom: 20px; }
    </style>
</head>
<body>
    @php
        $namaPondok = $tenant?->settings['ponpes_name'] ?? $tenant?->name ?? 'Pesantren';
    @endphp

    <div class="header">
        <h1>SURAT KETERANGAN DITERIMA</h1>
        <h2>PPDB {{ $namaPondok }}</h2>
        <p>Nomor: {{ $pendaftaran->nomor_pendaftaran }}/SK-PPDB/{{ now()->year }}</p>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, Panitia PPDB {{ $namaPondok }}, menerangkan bahwa:</p>

        <table>
            <tr><td class="label">Nama Lengkap</td><td>: {{ $pendaftaran->nama_lengkap }}</td></tr>
            <tr><td class="label">Tempat / Tgl Lahir</td><td>: {{ $pendaftaran->tempat_lahir ?: '-' }} / {{ $pendaftaran->tanggal_lahir?->translatedFormat('d M Y') ?: '-' }}</td></tr>
            <tr><td class="label">Jenis Kelamin</td><td>: {{ $pendaftaran->jenis_kelamin }}</td></tr>
            <tr><td class="label">No. Pendaftaran</td><td>: {{ $pendaftaran->nomor_pendaftaran }}</td></tr>
            <tr><td class="label">Gelombang</td><td>: {{ $pendaftaran->gelombang?->nama ?? '-' }}</td></tr>
        </table>

        <p><strong>DINYATAKAN DITERIMA</strong> sebagai calon santri di {{ $namaPondok }}.</p>
        <p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    </div>

    <table class="ttd">
        <tr>
            <td>Hormat Kami,<br><br><br><br><br>Panitia PPDB</td>
            <td>Mengetahui,<br><br><br><br><br>Pimpinan Pondok</td>
        </tr>
    </table>
</body>
</html>
