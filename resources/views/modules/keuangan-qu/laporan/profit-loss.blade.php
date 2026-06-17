<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Laporan Laba / Rugi</h2>
                <div class="text-secondary mt-1">Periode {{ str_pad((string) $month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</div>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" @selected($report['year'] === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                    <select name="month" class="form-select" onchange="this.form.submit()">
                        @foreach (['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $i => $m)
                            <option value="{{ $i + 1 }}" @selected($report['month'] === $i + 1)>{{ $m }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('keuangan.laporan.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pendapatan</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th class="text-end">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['pendapatan'] as $item)
                                <tr>
                                    <td>{{ $item['account']->code }}</td>
                                    <td>{{ $item['account']->name }}</td>
                                    <td class="text-end">{{ number_format($item['amount'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-secondary text-center">Tidak ada pendapatan.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-active fw-semibold">
                                <td colspan="2" class="text-end">Total Pendapatan</td>
                                <td class="text-end">Rp {{ number_format($report['total_pendapatan'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Beban</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th class="text-end">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['beban'] as $item)
                                <tr>
                                    <td>{{ $item['account']->code }}</td>
                                    <td>{{ $item['account']->name }}</td>
                                    <td class="text-end">{{ number_format($item['amount'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-secondary text-center">Tidak ada beban.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-active fw-semibold">
                                <td colspan="2" class="text-end">Total Beban</td>
                                <td class="text-end">Rp {{ number_format($report['total_beban'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="text-secondary small text-uppercase fw-semibold mb-1">Laba / Rugi Bersih</div>
                    @php
                        $labaRugi = $report['laba_rugi'];
                        $isProfit = $labaRugi >= 0;
                    @endphp
                    <div class="h1 mb-0 {{ $isProfit ? 'text-success' : 'text-danger' }}">
                        {{ $isProfit ? 'Laba' : 'Rugi' }}: Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
