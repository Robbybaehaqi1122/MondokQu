@include('exports.pdf.letterhead')

    <div class="title">LAPORAN TAGIHAN SANTRI</div>
    <div class="subtitle-text">Per {{ now()->translatedFormat('d F Y') }}</div>

    @if (count($invoices) > 0)
        <div class="summary">
            <table>
                <tr><td>Total Tagihan</td><td>: {{ count($invoices) }} tagihan</td></tr>
                <tr><td>Total Nominal</td><td>: Rp {{ number_format(collect($invoices)->sum(fn ($i) => $i->amount) / 100, 0, ',', '.') }}</td></tr>
                <tr><td>Total Terbayar</td><td>: Rp {{ number_format(collect($invoices)->sum(fn ($i) => $i->paid_amount) / 100, 0, ',', '.') }}</td></tr>
                <tr><td>Tanggal Cetak</td><td>: {{ now()->translatedFormat('d F Y H:i') }}</td></tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Judul Tagihan</th>
                    <th>Santri</th>
                    <th>Periode</th>
                    <th>Jatuh Tempo</th>
                    <th>Nominal (Rp)</th>
                    <th>Terbayar (Rp)</th>
                    <th>Sisa (Rp)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $index => $inv)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->title }}</td>
                        <td>{{ $inv->santri?->full_name ?? '-' }}</td>
                        <td class="text-center">{{ $inv->period_month }}/{{ $inv->period_year }}</td>
                        <td class="text-center">{{ $inv->due_date?->translatedFormat('d/m/Y') ?? '-' }}</td>
                        <td class="text-right">{{ number_format($inv->amount / 100, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($inv->paid_amount / 100, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($inv->outstandingAmount() / 100, 0, ',', '.') }}</td>
                        <td>{{ $inv->statusLabel() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center text-secondary">Tidak ada data tagihan.</p>
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
