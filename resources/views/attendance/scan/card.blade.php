<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Barcode - {{ $santri->full_name }}</title>
    <style>
        @page {
            size: 85.6mm 54mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', monospace;
            width: 85.6mm;
            height: 54mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            padding: 4mm;
            box-sizing: border-box;
            background: #fff;
        }
        .avatar {
            width: 28mm;
            height: 28mm;
            border-radius: 50%;
            object-fit: cover;
            background: #0d9488;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14mm;
            font-weight: bold;
            flex-shrink: 0;
            overflow: hidden;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .info {
            flex: 1;
            padding-left: 4mm;
            overflow: hidden;
        }
        .name {
            font-size: 4.5mm;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 1mm;
        }
        .detail {
            font-size: 3mm;
            color: #666;
            line-height: 1.3;
        }
        .barcode-img {
            margin-top: 2mm;
            text-align: center;
        }
        .barcode-img svg {
            width: 100%;
            max-width: 50mm;
            height: auto;
        }
        .barcode-text {
            font-size: 2.5mm;
            text-align: center;
            color: #333;
            margin-top: 0.5mm;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="avatar">
            @if ($santri->photoUrl())
                <img src="{{ $santri->photoUrl() }}" alt="{{ $santri->full_name }}">
            @else
                {{ strtoupper(substr($santri->full_name, 0, 1)) }}
            @endif
        </div>
        <div class="info">
            <div class="name">{{ $santri->full_name }}</div>
            <div class="detail">
                @if ($santri->nis)
                    NIS: {{ $santri->nis }}<br>
                @endif
                @if ($santri->room)
                    {{ $santri->room->name }}
                @endif
            </div>
            <div class="barcode-img">
                <img src="{{ route('attendance.scan.barcode-image', $santri) }}" alt="QR">
            </div>
            <div class="barcode-text">{{ $santri->barcode }}</div>
        </div>
    </div>
</body>
</html>
