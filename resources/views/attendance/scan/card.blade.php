<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Barcode - {{ $santri->full_name }}</title>
    <style>
        @page {
            size: 80mm 55mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            width: 80mm;
            height: 55mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }
        .card {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3mm;
            box-sizing: border-box;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 2mm;
        }
        .name {
            font-size: 4mm;
            font-weight: bold;
            line-height: 1.2;
        }
        .detail {
            font-size: 3mm;
            color: #555;
            margin-top: 0.5mm;
        }
        .qr-wrap {
            width: 38mm;
            height: 38mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qrcode {
            width: 100%;
            height: auto;
            max-width: 38mm;
            max-height: 38mm;
        }
        .barcode-text {
            font-size: 3.5mm;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            text-align: center;
            color: #333;
            margin-top: 1mm;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="name">{{ $santri->full_name }}</div>
            <div class="detail">
                @if ($santri->nis)NIS {{ $santri->nis }}@endif
                @if ($santri->nis && $santri->room) &middot; @endif
                @if ($santri->room){{ $santri->room->name }}@endif
            </div>
        </div>
        <div class="qr-wrap">
            <img class="qrcode" src="{{ route('attendance.scan.barcode-image', $santri) }}" alt="QR {{ $santri->barcode }}">
        </div>
        <div class="barcode-text">{{ $santri->barcode }}</div>
    </div>
</body>
</html>
