<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Jurnal Transaksi</h2>
                <div class="text-secondary mt-1">Pencatatan pemasukan & pengeluaran harian.</div>
            </div>
            <a href="{{ route('keuangan.jurnal.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Jurnal Baru
            </a>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
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
                        @foreach (['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $i => $m)
                            @if ($i > 0)
                                <option value="{{ $i }}" @selected($month === $i)>{{ $m }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="No. jurnal atau deskripsi..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                    <a href="{{ route('keuangan.jurnal.index') }}" class="btn btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Total Debit</th>
                        <th>Total Kredit</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>
                                <a href="{{ route('keuangan.jurnal.show', $entry) }}" class="text-reset fw-semibold">
                                    {{ $entry->journal_number }}
                                </a>
                            </td>
                            <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td class="text-truncate" style="max-width: 250px;">{{ $entry->description }}</td>
                            <td>Rp {{ number_format($entry->totalDebit(), 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($entry->totalKredit(), 0, ',', '.') }}</td>
                            <td>
                                @if ($entry->isPosted())
                                    <span class="badge bg-success">Posted</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                            <td>{{ $entry->creator?->name ?? '-' }}<br><span class="text-secondary small">{{ $entry->created_at->format('d/m/Y H:i') }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('keuangan.jurnal.show', $entry) }}" class="btn btn-icon btn-outline-primary btn-sm" title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if ($entry->isDraft())
                                        <form action="{{ route('keuangan.jurnal.destroy', $entry) }}" method="POST"
                                            onsubmit="return confirm('Hapus jurnal ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-secondary text-center py-4">Belum ada jurnal untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($entries->hasPages())
            <div class="card-footer">{{ $entries->links() }}</div>
        @endif
    </div>
</x-app-layout>
