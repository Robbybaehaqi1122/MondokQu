<x-app-layout>
    @php
        $statusBadgeClasses = [
            'pending' => 'bg-warning-lt text-warning',
            'approved' => 'bg-success-lt text-success',
            'rejected' => 'bg-danger-lt text-danger',
            'completed' => 'bg-info-lt text-info',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Pengajuan Izin Santri</h2>
            <div class="text-secondary mt-1">Kelola izin masuk, keluar, atau cuti santri.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Menunggu</div>
                <div class="fs-2 fw-bold">{{ number_format($stats['pending']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Disetujui</div>
                <div class="fs-2 fw-bold">{{ number_format($stats['approved']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Ditolak</div>
                <div class="fs-2 fw-bold">{{ number_format($stats['rejected']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Selesai</div>
                <div class="fs-2 fw-bold">{{ number_format($stats['completed']) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                <div>
                    <h3 class="card-title">Daftar Pengajuan Izin</h3>
                    <div class="text-secondary small mt-2">Menampilkan {{ $leaveRequests->total() }} pengajuan berdasarkan filter.</div>
                </div>

                @can('create izin')
                <button
                    type="button"
                    class="btn btn-primary"
                    id="open-create-leave-request-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#createLeaveRequestModal"
                >
                    Ajukan Izin
                </button>
                @endcan
            </div>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('pengurus.izin.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="q" class="form-label">Cari Santri</label>
                    <input id="q" name="q" type="text" class="form-control" value="{{ $filters['q'] }}" placeholder="Nama santri atau NIS">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption['value'] }}" @selected($filters['status'] === $statusOption['value'])>
                                {{ $statusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="santri" class="form-label">Santri</label>
                    <select id="santri" name="santri" class="form-select">
                        <option value="">Semua Santri</option>
                        @foreach ($santris as $santri)
                            <option value="{{ $santri->id }}" @selected($filters['santri'] == $santri->id)>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('pengurus.izin.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Tanggal Izin</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaveRequests as $leaveRequest)
                        @php
                            $santri = $leaveRequest->santri;
                            $badgeClass = $statusBadgeClasses[$leaveRequest->status] ?? 'bg-secondary-lt text-secondary';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $santri->full_name }}</div>
                                <div class="text-secondary small">NIS: {{ $santri->nis }}</div>
                            </td>
                            <td>
                                <div>{{ \Illuminate\Support\Carbon::parse($leaveRequest->start_date)->translatedFormat('d M Y') }}</div>
                                @if ($leaveRequest->end_date && ! $leaveRequest->start_date->isSameDay($leaveRequest->end_date))
                                    <div class="text-secondary small">s/d {{ \Illuminate\Support\Carbon::parse($leaveRequest->end_date)->translatedFormat('d M Y') }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="text-small">{{ \Illuminate\Support\Str::limit($leaveRequest->reason, 100) }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $leaveRequest->statusLabel() }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    @can('create izin')
                                        @if ($leaveRequest->status === 'pending')
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editLeaveRequestModal{{ $leaveRequest->id }}"
                                            >
                                                Edit
                                            </button>
                                        @endif
                                    @endcan

                                    @can('approve izin')
                                        @if ($leaveRequest->status === 'pending')
                                            <form method="POST" action="{{ route('pengurus.izin.approve', $leaveRequest) }}" onsubmit="return confirm('Setujui pengajuan izin ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm w-100">Setujui</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('approve izin')
                                        @if ($leaveRequest->status === 'pending')
                                            <form method="POST" action="{{ route('pengurus.izin.reject', $leaveRequest) }}" onsubmit="return confirm('Tolak pengajuan izin ini?')">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm w-100">Tolak</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('approve izin')
                                        @if ($leaveRequest->status === 'approved')
                                            <form method="POST" action="{{ route('pengurus.izin.complete', $leaveRequest) }}" onsubmit="return confirm('Tandai sebagai selesai?')">
                                                @csrf
                                                <button type="submit" class="btn btn-info btn-sm w-100">Selesai</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('create izin')
                                        @if (in_array($leaveRequest->status, ['pending', 'rejected']))
                                            <form method="POST" action="{{ route('pengurus.izin.destroy', $leaveRequest) }}" onsubmit="return confirm('Hapus pengajuan izin ini?')" class="w-100">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0">Hapus</button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-secondary">Belum ada pengajuan izin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leaveRequests->hasPages())
            <div class="card-footer">
                {{ $leaveRequests->links() }}
            </div>
        @endif
    </div>

    <!-- Create Leave Request Modal -->
    <div class="modal modal-blur fade" id="createLeaveRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('pengurus.izin.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Ajukan Izin Santri</h5>
                            <div class="text-secondary mt-1">Isi formulir berikut untuk mengajukan izin masuk, keluar, atau cuti untuk santri.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @include('pengurus.leave_requests.partials.form-fields', [
                            'formPrefix' => 'create',
                            'leaveRequest' => null,
                            'santris' => $santris,
                            'errorBag' => $errors->createLeaveRequest,
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ajukan Izin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Leave Request Modal -->
    @foreach ($leaveRequests as $leaveRequest)
        <div class="modal modal-blur fade" id="editLeaveRequestModal{{ $leaveRequest->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('pengurus.izin.update', $leaveRequest) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="editing_leave_request_id" value="{{ $leaveRequest->id }}">

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Edit Pengajuan Izin</h5>
                                <div class="text-secondary mt-1">{{ $leaveRequest->santri->full_name }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @include('pengurus.leave_requests.partials.form-fields', [
                                'formPrefix' => 'edit_' . $leaveRequest->id,
                                'leaveRequest' => $leaveRequest,
                                'santris' => $santris,
                                'errorBag' => $errors->updateLeaveRequest,
                            ])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->createLeaveRequest->any())
                document.getElementById('open-create-leave-request-modal')?.click();
            @endif

            @if ($errors->updateLeaveRequest->any() && old('editing_leave_request_id'))
                const editModalElement = document.getElementById('editLeaveRequestModal{{ old('editing_leave_request_id') }}');
                if (editModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(editModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
