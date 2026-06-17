<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Laporan Arus Kas</h2>
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Arus Masuk</div>
                    <div class="h1 text-success mb-0">Rp {{ number_format($report['pemasukan'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Arus Keluar</div>
                    <div class="h1 text-danger mb-0">Rp {{ number_format($report['pengeluaran'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="text-secondary small text-uppercase fw-semibold mb-2">Arus Kas Bersih</div>
                    @php $bersih = $report['arus_bersih']; @endphp
                    <div class="h1 {{ $bersih >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                        Rp {{ number_format(abs($bersih), 0, ',', '.') }}
                    </div>
                    <div class="text-secondary small">{{ $bersih >= 0 ? 'Surplus' : 'Defisit' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cards mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Arus Kas</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <tbody>
                            <tr class="fw-semibold">
                                <td>Saldo Awal Periode</td>
                                <td class="text-end">Rp -</td>
                            </tr>
                            <tr class="text-success">
                                <td>Pemasukan (Kas & Bank)</td>
                                <td class="text-end">Rp {{ number_format($report['pemasukan'], 0, ',', '.') }}</td>
                            </tr>
                            <tr class="text-danger">
                                <td>Pengeluaran (Kas & Bank)</td>
                                <td class="text-end">(Rp {{ number_format($report['pengeluaran'], 0, ',', '.') }})</td>
                            </tr>
                            <tr class="table-active fw-semibold">
                                <td>Arus Kas Bersih</td>
                                <td class="text-end {{ $bersih >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format(abs($bersih), 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="fw-semibold">
                                <td>Saldo Akhir Periode</td>
                                <td class="text-end">Rp {{ number_format($report['pemasukan'] - $report['pengeluaran'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
