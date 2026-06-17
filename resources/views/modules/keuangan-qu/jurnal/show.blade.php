<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Jurnal: {{ $journalEntry->journal_number }}</h2>
                <div class="text-secondary mt-1">Detail jurnal transaksi.</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('keuangan.kwitansi.pdf', $journalEntry) }}" class="btn btn-outline-primary" target="_blank">
                    <i class="ti ti-file-text me-1"></i> Kwitansi PDF
                </a>
                @if ($journalEntry->isDraft())
                    <form action="{{ route('keuangan.jurnal.approve', $journalEntry) }}" method="POST"
                        onsubmit="return confirm('Posting jurnal ini? Tindakan ini tidak bisa dibatalkan.')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i> Posting Jurnal
                        </button>
                    </form>
                @endif
                <a href="{{ route('keuangan.jurnal.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Jurnal</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-secondary small">No. Jurnal</div>
                            <div class="fw-semibold">{{ $journalEntry->journal_number }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-secondary small">Tanggal</div>
                            <div class="fw-semibold">{{ $journalEntry->entry_date->format('d F Y') }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-secondary small">Periode</div>
                            <div class="fw-semibold">{{ $journalEntry->period_month }} / {{ $journalEntry->period_year }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-secondary small">Status</div>
                            <div>
                                @if ($journalEntry->isPosted())
                                    <span class="badge bg-success">Posted</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="text-secondary small">Deskripsi</div>
                            <div>{{ $journalEntry->description }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-secondary small">Dibuat Oleh</div>
                            <div>{{ $journalEntry->creator?->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-secondary small">Disetujui Oleh</div>
                            <div>{{ $journalEntry->approver?->name ?? '-' }}</div>
                        </div>
                        @if ($journalEntry->approved_at)
                            <div class="col-md-3">
                                <div class="text-secondary small">Disetujui Pada</div>
                                <div>{{ $journalEntry->approved_at->format('d/m/Y H:i') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Transaksi</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Debit (Rp)</th>
                                <th class="text-end">Kredit (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($journalEntry->details as $detail)
                                <tr>
                                    <td>{{ $detail->coaAccount->code }}</td>
                                    <td>{{ $detail->coaAccount->name }}</td>
                                    <td>{{ $detail->description ?? '-' }}</td>
                                    <td class="text-end">
                                        {{ $detail->debit > 0 ? 'Rp ' . number_format($detail->debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $detail->kredit > 0 ? 'Rp ' . number_format($detail->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-active fw-semibold">
                                <td colspan="3" class="text-end">Total:</td>
                                <td class="text-end">Rp {{ number_format($journalEntry->totalDebit(), 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($journalEntry->totalKredit(), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
