<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Trail Logs</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h1 { font-size: 16px; margin-bottom: 5px; }
        .meta { color: #666; margin-bottom: 15px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; font-size: 9px; }
        td { font-size: 8px; }
        .method-post { color: #2d7d2d; }
        .method-put { color: #1a6ba8; }
        .method-patch { color: #c47a1a; }
        .method-delete { color: #c41a1a; }
    </style>
</head>
<body>
    <h1>Audit Trail Request</h1>
    <div class="meta">Dicetak: {{ now()->translatedFormat('d F Y H:i:s') }} &middot; Total: {{ $logs->count() }} log</div>

    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>User</th>
                <th>Method</th>
                <th>URL</th>
                <th>Status</th>
                <th>Durasi</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                    <td>{{ $log->user?->name ?? 'Guest / System' }}</td>
                    <td class="method-{{ strtolower($log->method) }}">{{ $log->method }}</td>
                    <td>{{ $log->url }}</td>
                    <td>{{ $log->response_status ?? '-' }}</td>
                    <td>{{ $log->duration_ms !== null ? ($log->duration_ms >= 1000 ? number_format($log->duration_ms / 1000, 2) . 's' : $log->duration_ms . 'ms') : '-' }}</td>
                    <td>{{ $log->ip_address ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>