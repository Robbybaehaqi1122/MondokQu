<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Laporan Aset Per Kategori</h2>
            </div>
            <div>
                <a href="{{ route('inventaris.laporan.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Jumlah Aset</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td>{{ number_format($item->asets_count) }}</td>
                            <td>Rp {{ number_format($item->asets_sum_harga_perolehan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
