<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between">
            <div>
                <h2 class="page-title mt-1">Setoran Hafalan</h2>
                <div class="text-secondary small">Catat dan review setoran hafalan kitab santri.</div>
            </div>
            <a href="{{ route('kitab.setoran.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Tambah Setoran
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santris as $s)
                            <option value="{{ $s->id }}" @selected(request('santri') == $s->id)>{{ $s->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="kitab" class="form-select">
                        <option value="">Semua Kitab</option>
                        @foreach ($kitabs as $k)
                            <option value="{{ $k->id }}" @selected(request('kitab') == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                        <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
                @if (request()->anyFilled(['santri', 'kitab', 'status']))
                    <div class="col-12">
                        <a href="{{ route('kitab.setoran.index') }}" class="btn btn-ghost-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Kitab</th>
                        <th>Tanggal</th>
                        <th>Materi</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($setorans as $setoran)
                        <tr>
                            <td class="fw-semibold">{{ $setoran->santri?->full_name ?? '-' }}</td>
                            <td>{{ $setoran->kitab?->nama ?? '-' }}</td>
                            <td class="text-secondary">{{ $setoran->tanggal_setoran?->translatedFormat('d M Y') }}</td>
                            <td>{{ $setoran->materi ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $setoran->status === 'disetujui' ? 'bg-success' : ($setoran->status === 'ditolak' ? 'bg-danger' : 'bg-warning-lt') }}">
                                    {{ ucfirst($setoran->status) }}
                                </span>
                            </td>
                            <td>
                                @if ($setoran->status === 'pending')
                                    <div class="d-flex gap-1">
                                        <form method="POST" action="{{ route('kitab.setoran.approve', $setoran) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Setujui</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejectSetoran('{{ $setoran->id }}')">Tolak</button>
                                    </div>
                                @else
                                    <span class="text-secondary small">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-secondary">Belum ada setoran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($setorans->hasPages())
            <div class="card-footer d-flex justify-content-center">{{ $setorans->links() }}</div>
        @endif
    </div>

    {{-- Reject Modal --}}
    <div class="modal modal-blur fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="rejectForm">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tolak Setoran</h5>
                            <div class="text-secondary small mt-1">Berikan alasan penolakan (opsional).</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Alasan penolakan..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Setoran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function rejectSetoran(id) {
            document.getElementById('rejectForm').action = '{{ url("kitab/setoran") }}/' + id + '/reject';
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>
</x-app-layout>
