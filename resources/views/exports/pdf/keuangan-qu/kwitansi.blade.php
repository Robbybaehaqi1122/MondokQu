<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $entry->journal_number }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #0d9488; }
        .header h1 { font-size: 18px; color: #0d9488; margin: 0 0 5px; }
        .header .sub { font-size: 10px; color: #6b7280; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin: 20px 0; text-transform: uppercase; letter-spacing: 2px; color: #0d9488; }
        .info { margin-bottom: 15px; }
        .info table { width: 100%; }
        .info td { padding: 2px 5px; }
        .info .label { width: 120px; color: #6b7280; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background: #0d9488; color: white; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items th.right { text-align: right; }
        table.items td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }
        table.items td.right { text-align: right; }
        table.items tr.total td { font-weight: bold; border-top: 2px solid #0d9488; font-size: 12px; }
        .footer { margin-top: 30px; }
        .footer table { width: 100%; }
        .footer td { width: 50%; }
        .signature { text-align: center; margin-top: 10px; }
        .signature .line { margin-top: 40px; padding-top: 5px; border-top: 1px solid #374151; display: inline-block; min-width: 180px; font-size: 11px; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 60px; opacity: 0.04; color: #0d9488; font-weight: bold; text-transform: uppercase; z-index: -1; }
        .stamp { text-align: right; margin-top: -30px; }
        .stamp-box { display: inline-block; border: 2px dashed #0d9488; padding: 5px 15px; color: #0d9488; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="watermark">MONDOK QU</div>

    <div class="header">
        <h1>{{ config('app.ponpes_name') }}</h1>
        <div class="sub">{{ config('app.ponpes_address') }} | Telp: {{ config('app.ponpes_phone') }} | Email: {{ config('app.ponpes_email') }}</div>
    </div>

    <div class="title">Kwitansi Pembayaran</div>

    <div class="info">
        <table>
            <tr><td class="label">No. Kwitansi</td><td>: <strong>{{ $entry->journal_number }}</strong></td></tr>
            <tr><td class="label">Tanggal</td><td>: {{ $entry->entry_date->format('d F Y') }}</td></tr>
            <tr><td class="label">Keterangan</td><td>: {{ $entry->description }}</td></tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Kode Akun</th>
                <th>Nama Akun</th>
                <th>Deskripsi</th>
                <th class="right">Debit (Rp)</th>
                <th class="right">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($entry->details as $detail)
                <tr>
                    <td>{{ $detail->coaAccount->code }}</td>
                    <td>{{ $detail->coaAccount->name }}</td>
                    <td>{{ $detail->description ?? '-' }}</td>
                    <td class="right">{{ $detail->debit > 0 ? number_format($detail->debit, 0, ',', '.') : '-' }}</td>
                    <td class="right">{{ $detail->kredit > 0 ? number_format($detail->kredit, 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="3" style="text-align: right;">Total</td>
                <td class="right">Rp {{ number_format($entry->totalDebit(), 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($entry->totalKredit(), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="text-align: center; font-style: italic; color: #6b7280; margin: 10px 0;">
        Kwitansi ini sah dan telah diproses oleh sistem Mondok Qu.
    </p>

    <div class="footer">
        <table>
            <tr>
                <td style="text-align: center;">
                    <div>Dibuat Oleh,</div>
                    <div class="signature">
                        <div class="line">{{ $entry->creator?->name ?? '_______' }}</div>
                    </div>
                </td>
                <td style="text-align: center;">
                    <div>Mengetahui,</div>
                    <div class="signature">
                        <div class="line">{{ config('app.ponpes_treasurer') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 10px; text-align: center; color: #9ca3af; font-size: 8px;">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} | Sistem Informasi Pondok Mondok Qu
    </div>
</body>
</html>
