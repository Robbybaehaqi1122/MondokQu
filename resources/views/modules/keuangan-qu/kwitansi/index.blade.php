<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Kwitansi Digital</h2>
            <div class="text-secondary mt-1">Generate dan cetak kwitansi dari jurnal yang sudah diposting.</div>
        </div>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No. Jurnal</th>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Total</th>
                        <th>Dibuat</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="fw-semibold">{{ $entry->journal_number }}</td>
                            <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td class="text-truncate" style="max-width: 300px;">{{ $entry->description }}</td>
                            <td>Rp {{ number_format($entry->totalDebit(), 0, ',', '.') }}</td>
                            <td>{{ $entry->creator?->name ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('keuangan.kwitansi.pdf', $entry) }}" class="btn btn-icon btn-outline-primary btn-sm" target="_blank" title="Lihat Kwitansi">
                                        <i class="ti ti-file-text"></i>
                                    </a>
                                    <a href="{{ route('keuangan.kwitansi.download', $entry) }}" class="btn btn-icon btn-outline-success btn-sm" title="Download Kwitansi">
                                        <i class="ti ti-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary text-center py-4">
                                Belum ada jurnal yang diposting. <a href="{{ route('keuangan.jurnal.create') }}">Buat jurnal baru</a>.
                            </td>
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
