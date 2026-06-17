<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Buku Besar</h2>
                <div class="text-secondary mt-1">
                    @if ($selectedAccount)
                        Akun: {{ $selectedAccount->code }} - {{ $selectedAccount->name }}
                    @else
                        Semua akun
                    @endif
                </div>
            </div>
            <div>
                <a href="{{ route('keuangan.laporan.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Akun</label>
                    <select name="coa_account_id" class="form-select">
                        <option value="">-- Semua Akun --</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}" @selected(request('coa_account_id') == $acc->id)>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        <option value="">Semua</option>
                        @foreach (['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $i => $m)
                            <option value="{{ $i + 1 }}" @selected((int) request('month') === $i + 1)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Tampilkan</button>
                    <a href="{{ route('keuangan.laporan.ledger') }}" class="btn btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            </form>
        </div>
    </div>

    @forelse ($entries as $accountName => $details)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">{{ $accountName }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Jurnal</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Debit (Rp)</th>
                            <th class="text-end">Kredit (Rp)</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningBalance = 0; @endphp
                        @foreach ($details as $detail)
                            @php
                                $account = $detail->coaAccount;
                                if ($account->normal_balance === 'debit') {
                                    $runningBalance += $detail->debit - $detail->kredit;
                                } else {
                                    $runningBalance += $detail->kredit - $detail->debit;
                                }
                            @endphp
                            <tr>
                                <td>{{ $detail->journalEntry->entry_date->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('keuangan.jurnal.show', $detail->journalEntry) }}" class="text-reset">
                                        {{ $detail->journalEntry->journal_number }}
                                    </a>
                                </td>
                                <td>{{ $detail->description ?? $detail->journalEntry->description }}</td>
                                <td class="text-end">{{ $detail->debit > 0 ? number_format($detail->debit, 0, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ $detail->kredit > 0 ? number_format($detail->kredit, 0, ',', '.') : '-' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-active fw-semibold">
                            <td colspan="3" class="text-end">Saldo Akhir</td>
                            <td class="text-end">{{ number_format($details->sum('debit'), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($details->sum('kredit'), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti ti-search text-secondary" style="font-size: 2rem;"></i>
                <p class="text-secondary mt-2">Tidak ada data untuk filter yang dipilih.</p>
            </div>
        </div>
    @endforelse
</x-app-layout>
