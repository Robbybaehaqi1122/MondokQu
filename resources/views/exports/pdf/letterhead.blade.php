<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11pt; color: #1a1a1a; }
        .letterhead { text-align: center; border-bottom: 2px solid #1a56db; padding-bottom: 10px; margin-bottom: 20px; }
        .letterhead .institution { font-size: 16pt; font-weight: bold; color: #1a56db; }
        .letterhead .subtitle { font-size: 9pt; color: #6b7280; margin-top: 3px; }
        .letterhead .address { font-size: 8pt; color: #6b7280; margin-top: 2px; }
        .title { text-align: center; font-size: 13pt; font-weight: bold; margin-bottom: 15px; text-decoration: underline; }
        .subtitle-text { text-align: center; font-size: 9pt; color: #6b7280; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 15px; }
        th { background-color: #1a56db; color: white; padding: 6px 4px; text-align: left; font-weight: bold; font-size: 8pt; }
        td { padding: 4px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 8pt; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 5px; }
        .signature { margin-top: 30px; display: flex; justify-content: flex-end; }
        .signature-content { text-align: center; font-size: 9pt; }
        .signature-content .city-date { margin-bottom: 60px; }
        .summary { margin-bottom: 15px; }
        .summary table { width: auto; }
        .summary td { border: none; padding: 2px 10px; font-size: 9pt; }
        .summary td:first-child { font-weight: bold; }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="institution">PONDOK PESANTREN {{ config('app.ponpes_name', 'Mondok Qu') }}</div>
        <div class="subtitle">{{ config('app.ponpes_address', 'Jl. Pendidikan No. 1, Kota Santri') }}</div>
        <div class="address">Telp: {{ config('app.ponpes_phone', '(021) 1234-5678') }} &middot; Email: {{ config('app.ponpes_email', 'info@mondok-qu.sch.id') }}</div>
    </div>
