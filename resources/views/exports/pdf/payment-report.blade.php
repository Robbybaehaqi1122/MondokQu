@include('exports.pdf.letterhead')

    <div class="title">LAPORAN PEMBAYARAN</div>
    <div class="subtitle-text">Periode {{ $dateFrom->translatedFormat('d F Y') }} &mdash; {{ $dateTo->translatedFormat('d F Y') }}</div>

    @if (count($payments) > 0)
        <div class="summary">
            <table>
                <tr><td>Total Transaksi</td><td>: {{ count($payments) }} pembayaran</td></tr>
                <tr><td>Total Nominal</td><td>: Rp {{ number_format(collect($payments)->sum(fn ($p) => $p->amount) / 100, 0, ',', '.') }}</td></tr>
                <tr><td>Periode</td><td>: {{ $dateFrom->translatedFormat('d/m/Y') }} &mdash; {{ $dateTo->translatedFormat('d/m/Y') }}</td></tr>
                <tr><td>Tanggal Cetak</td><td>: {{ now()->translatedFormat('d F Y H:i') }}</td></tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tgl Bayar</th>
                    <th>Invoice</th>
                    <th>Santri</th>
                    <th>Metode</th>
                    <th>Nominal (Rp)</th>
                    <th>Referensi</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $index => $payment)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $payment->paid_at?->translatedFormat('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $payment->invoice?->invoice_number ?? '-' }}</td>
                        <td>{{ $payment->santri?->full_name ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::headline($payment->payment_method) }}</td>
                        <td class="text-right">{{ number_format($payment->amount / 100, 0, ',', '.') }}</td>
                        <td>{{ $payment->reference_number ?? '-' }}</td>
                        <td>{{ $payment->recorder?->name ?? 'System' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center text-secondary">Tidak ada data pembayaran pada periode ini.</p>
    @endif

    <div class="signature">
        <div class="signature-content">
            <div class="city-date">{{ config('app.ponpes_city', 'Kota Santri') }}, {{ now()->translatedFormat('d F Y') }}</div>
            <div style="margin-bottom: 60px;">Bendahara</div>
            <div style="margin-top: 5px; font-weight: bold;">{{ config('app.ponpes_treasurer', '___________________') }}</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh {{ config('app.name', 'Mondok Qu') }} &mdash; {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>
</html>
