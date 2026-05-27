@php
    $displayStatus = $invoice->isOverdue() ? 'overdue' : $invoice->status;
    $statusLabel = $invoice->statusLabel();
    $invoiceAmount = (float) $invoice->amount;
    $paidAmount = (float) $invoice->paid_amount;
    $outstandingAmount = $invoice->outstandingAmount();
    $periodLabel = $invoice->period_month && $invoice->period_year
        ? str_pad((string) $invoice->period_month, 2, '0', STR_PAD_LEFT).'/'.$invoice->period_year
        : '-';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Kwitansi {{ $invoice->invoice_number }}</title>
        <style>
            :root {
                color-scheme: light;
                --border: #d8dee8;
                --muted: #64748b;
                --text: #0f172a;
                --soft: #f8fafc;
                --brand: #206bc4;
                --success: #2fb344;
                --warning: #f59f00;
                --danger: #d63939;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: #edf2f7;
                color: var(--text);
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.45;
            }

            .receipt-actions {
                width: min(100%, 860px);
                margin: 20px auto 12px;
                padding: 0 16px;
                display: flex;
                justify-content: flex-end;
                gap: 8px;
            }

            .btn {
                min-height: 38px;
                padding: 8px 14px;
                border: 1px solid var(--border);
                border-radius: 6px;
                background: #fff;
                color: var(--text);
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
            }

            .btn-primary {
                border-color: var(--brand);
                background: var(--brand);
                color: #fff;
            }

            .receipt-page {
                width: min(100%, 860px);
                margin: 0 auto 28px;
                padding: 32px;
                background: #fff;
                border: 1px solid var(--border);
                box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
            }

            .receipt-header {
                display: flex;
                justify-content: space-between;
                gap: 24px;
                padding-bottom: 20px;
                border-bottom: 2px solid var(--text);
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .brand img {
                width: 52px;
                height: 52px;
                object-fit: contain;
            }

            .brand-title {
                font-size: 22px;
                font-weight: 800;
            }

            .brand-meta,
            .receipt-meta {
                color: var(--muted);
                font-size: 12px;
            }

            .receipt-meta {
                text-align: right;
            }

            .receipt-title {
                margin: 28px 0 18px;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 18px;
            }

            h1 {
                margin: 0;
                font-size: 24px;
                line-height: 1.2;
            }

            .status {
                display: inline-block;
                min-width: 96px;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 800;
                text-align: center;
                color: #fff;
                background: var(--brand);
            }

            .status-paid {
                background: var(--success);
            }

            .status-pending,
            .status-partial {
                background: var(--warning);
            }

            .status-overdue {
                background: var(--danger);
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 12px;
            }

            .box {
                min-width: 0;
                padding: 12px;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: var(--soft);
            }

            .label {
                display: block;
                color: var(--muted);
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .value {
                display: block;
                margin-top: 5px;
                font-weight: 800;
                overflow-wrap: anywhere;
            }

            .summary {
                margin-top: 18px;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .summary .value {
                font-size: 18px;
            }

            .section-title {
                margin: 28px 0 10px;
                font-size: 15px;
                font-weight: 800;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid var(--border);
            }

            th,
            td {
                padding: 10px;
                border-bottom: 1px solid var(--border);
                text-align: left;
                vertical-align: top;
            }

            th {
                background: var(--soft);
                color: var(--muted);
                font-size: 11px;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            td:last-child,
            th:last-child {
                text-align: right;
            }

            tr:last-child td {
                border-bottom: 0;
            }

            .note {
                margin-top: 18px;
                padding: 12px;
                border: 1px solid var(--border);
                border-radius: 8px;
                background: #fff;
            }

            .receipt-footer {
                margin-top: 32px;
                display: flex;
                justify-content: space-between;
                gap: 24px;
                color: var(--muted);
                font-size: 12px;
            }

            .signature {
                min-width: 180px;
                text-align: center;
                color: var(--text);
            }

            .signature-line {
                margin-top: 56px;
                border-top: 1px solid var(--text);
                padding-top: 8px;
                font-weight: 700;
            }

            @media (max-width: 720px) {
                body {
                    font-size: 12px;
                }

                .receipt-page {
                    padding: 20px;
                    margin-bottom: 16px;
                }

                .receipt-header,
                .receipt-title,
                .receipt-footer {
                    flex-direction: column;
                }

                .receipt-meta {
                    text-align: left;
                }

                .grid,
                .summary {
                    grid-template-columns: minmax(0, 1fr);
                }

                th,
                td {
                    padding: 8px;
                }
            }

            @media print {
                @page {
                    margin: 14mm;
                }

                body {
                    background: #fff;
                    font-size: 12px;
                }

                .receipt-actions {
                    display: none !important;
                }

                .receipt-page {
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    border: 0;
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="receipt-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()">Cetak</button>
            <a href="{{ route('wali-santri.invoices.show', $invoice) }}" class="btn">Kembali</a>
        </div>

        <main class="receipt-page">
            <header class="receipt-header">
                <div class="brand">
                    <img src="{{ asset('images/mondok-qu-logo.png') }}" alt="Mondok Qu" loading="lazy">
                    <div>
                        <div class="brand-title">Mondok Qu</div>
                        <div class="brand-meta">{{ $invoice->tenant?->name ?? 'Portal Wali Santri' }}</div>
                    </div>
                </div>
                <div class="receipt-meta">
                    <div>No. Tagihan: <strong>{{ $invoice->invoice_number }}</strong></div>
                    <div>Dicetak: {{ now()->translatedFormat('d M Y H:i') }}</div>
                </div>
            </header>

            <section class="receipt-title">
                <div>
                    <h1>Bukti Pembayaran / Kwitansi</h1>
                    <div class="brand-meta">{{ $invoice->title }}</div>
                </div>
                <span class="status status-{{ $displayStatus }}">{{ $statusLabel }}</span>
            </section>

            <section class="grid">
                <div class="box">
                    <span class="label">Santri</span>
                    <span class="value">{{ $invoice->santri?->full_name ?? '-' }}</span>
                </div>
                <div class="box">
                    <span class="label">NIS</span>
                    <span class="value">{{ $invoice->santri?->nis ?? '-' }}</span>
                </div>
                <div class="box">
                    <span class="label">Periode</span>
                    <span class="value">{{ $periodLabel }}</span>
                </div>
                <div class="box">
                    <span class="label">Jatuh Tempo</span>
                    <span class="value">{{ $invoice->due_date?->translatedFormat('d M Y') ?? '-' }}</span>
                </div>
                <div class="box">
                    <span class="label">Kamar / Asrama</span>
                    <span class="value">{{ $invoice->santri?->displayRoomName() ?? '-' }}</span>
                </div>
                <div class="box">
                    <span class="label">Status</span>
                    <span class="value">{{ $statusLabel }}</span>
                </div>
            </section>

            <section class="grid summary">
                <div class="box">
                    <span class="label">Total Tagihan</span>
                    <span class="value">Rp {{ number_format($invoiceAmount, 0, ',', '.') }}</span>
                </div>
                <div class="box">
                    <span class="label">Sudah Dibayar</span>
                    <span class="value">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                </div>
                <div class="box">
                    <span class="label">Sisa Tagihan</span>
                    <span class="value">Rp {{ number_format($outstandingAmount, 0, ',', '.') }}</span>
                </div>
            </section>

            <section>
                <div class="section-title">Riwayat Pembayaran</div>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Metode</th>
                            <th>Referensi</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>
                                    {{ $payment->paid_at?->translatedFormat('d M Y H:i') ?? '-' }}
                                    @if ($payment->note)
                                        <div class="brand-meta">{{ $payment->note }}</div>
                                    @endif
                                </td>
                                <td>{{ $payment->payment_method ? str($payment->payment_method)->headline() : '-' }}</td>
                                <td>{{ $payment->reference_number ?: '-' }}</td>
                                <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Belum ada pembayaran untuk tagihan ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            @if ($invoice->notes)
                <section class="note">
                    <span class="label">Catatan Tagihan</span>
                    <span class="value">{{ $invoice->notes }}</span>
                </section>
            @endif

            <footer class="receipt-footer">
                <div>
                    <div>Dokumen ini dibuat dari Portal Wali Santri Mondok Qu.</div>
                    <div>{{ config('app.name', 'Mondok Qu') }}</div>
                </div>
                <div class="signature">
                    <div>{{ now()->translatedFormat('d M Y') }}</div>
                    <div class="signature-line">Admin Pondok</div>
                </div>
            </footer>
        </main>
    </body>
</html>
