@include('exports.pdf.letterhead')

    <div class="title">LAPORAN DATA SANTRI</div>
    <div class="subtitle-text">Per {{ now()->translatedFormat('d F Y') }}</div>

    @if (count($santris) > 0)
        <div class="summary">
            <table>
                <tr><td>Total Santri</td><td>: {{ count($santris) }} orang</td></tr>
                <tr><td>Tanggal Cetak</td><td>: {{ now()->translatedFormat('d F Y H:i') }}</td></tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>Nama Lengkap</th>
                    <th>L/P</th>
                    <th>Status</th>
                    <th>Kamar</th>
                    <th>Angkatan</th>
                    <th>Nama Wali</th>
                    <th>HP Wali</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($santris as $index => $s)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $s->nis }}</td>
                        <td>{{ $s->full_name }}</td>
                        <td class="text-center">{{ $s->gender === 'male' ? 'L' : 'P' }}</td>
                        <td>{{ $s->statusLabel() }}</td>
                        <td>{{ $s->displayRoomName('-') }}</td>
                        <td class="text-center">{{ $s->entry_year ?? '-' }}</td>
                        <td>{{ $s->displayGuardianName('-') }}</td>
                        <td>{{ $s->displayGuardianPhone('-') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-center text-secondary">Tidak ada data santri.</p>
    @endif

    <div class="signature">
        <div class="signature-content">
            <div class="city-date">{{ config('app.ponpes_city', 'Kota Santri') }}, {{ now()->translatedFormat('d F Y') }}</div>
            <div style="margin-bottom: 60px;">Kepala Pondok</div>
            <div style="margin-top: 5px; font-weight: bold;">{{ config('app.ponpes_headmaster', '___________________') }}</div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini digenerate secara otomatis oleh {{ config('app.name', 'Mondok Qu') }} &mdash; {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>
</html>
