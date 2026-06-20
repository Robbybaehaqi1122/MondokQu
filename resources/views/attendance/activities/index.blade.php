<x-app-layout>
    @php
        $statusBadgeClasses = [
            'active' => 'bg-success-lt text-success',
            'inactive' => 'bg-secondary-lt text-secondary',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Kegiatan & Absensi</h2>
            <div class="text-secondary mt-1">Kelola kegiatan dan jadwal dasar untuk AbsenQu.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Kegiatan</div>
                <div class="fs-2 fw-bold">{{ number_format($activityStats['total']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Aktif</div>
                <div class="fs-2 fw-bold">{{ number_format($activityStats['active']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Nonaktif</div>
                <div class="fs-2 fw-bold">{{ number_format($activityStats['inactive']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Aktif Hari Ini</div>
                <div class="fs-2 fw-bold">{{ number_format($activityStats['today']) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                <div>
                    <h3 class="card-title">Daftar Kegiatan</h3>
                    <div class="text-secondary small mt-2">Menampilkan {{ $activities->total() }} kegiatan berdasarkan filter aktif.</div>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="open-create-attendance-activity-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#createAttendanceActivityModal"
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Kegiatan
                </button>
            </div>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('attendance.activities.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label for="q" class="form-label">Cari Kegiatan</label>
                    <input id="q" name="q" type="text" class="form-control" value="{{ $filters['q'] }}" placeholder="Nama kegiatan atau catatan">
                </div>
                <div class="col-md-4 col-lg-3">
                    <label for="day" class="form-label">Hari</label>
                    <select id="day" name="day" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($dayOptions as $dayOption)
                            <option value="{{ $dayOption['value'] }}" @selected($filters['day'] === $dayOption['value'])>
                                {{ $dayOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption['value'] }}" @selected($filters['status'] === $statusOption['value'])>
                                {{ $statusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-filter me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('attendance.activities.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Jadwal</th>
                        <th>Hari Aktif</th>
                        <th>Penanggung Jawab</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $activity->name }}</div>
                                @if ($activity->description)
                                    <div class="text-secondary small">{{ $activity->description }}</div>
                                @endif
                            </td>
                            <td>{{ $activity->timeRangeLabel() }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($activity->active_days ?? [] as $activeDay)
                                        <span class="badge bg-blue-lt text-blue">{{ \App\Models\AttendanceActivity::dayLabels()[$activeDay] ?? ucfirst($activeDay) }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if ($activity->responsibleUser)
                                    <div>{{ $activity->responsibleUser->name }}</div>
                                    <div class="text-secondary small">{{ '@'.$activity->responsibleUser->username }}</div>
                                @else
                                    <span class="text-secondary small">Tidak ditentukan</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $statusBadgeClasses[$activity->status] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $activity->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editAttendanceActivityModal{{ $activity->id }}" aria-label="Edit kegiatan">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('attendance.activities.destroy', $activity) }}" onsubmit="return confirm('Hapus kegiatan absensi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Hapus kegiatan">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="modal modal-blur fade" id="editAttendanceActivityModal{{ $activity->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('attendance.activities.update', $activity) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="editing_attendance_activity_id" value="{{ $activity->id }}">

                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title">Edit Kegiatan Absensi</h5>
                                                        <div class="text-secondary small mt-1">{{ $activity->name }}</div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @include('attendance.activities.partials.form-fields', [
                                                        'formPrefix' => 'edit_'.$activity->id,
                                                        'activity' => $activity,
                                                        'statusOptions' => $statusOptions,
                                                        'dayOptions' => $dayOptions,
                                                        'responsibleUserOptions' => $responsibleUserOptions,
                                                        'errorBag' => $errors->updateAttendanceActivity,
                                                    ])
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="ti ti-device-floppy me-1"></i>
                                                        Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada kegiatan absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="card-footer">
                {{ $activities->links() }}
            </div>
        @endif
    </div>

    <div class="modal modal-blur fade" id="createAttendanceActivityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('attendance.activities.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Kegiatan Absensi</h5>
                            <div class="text-secondary small mt-1">Kegiatan ini menjadi dasar sesi absensi harian.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @include('attendance.activities.partials.form-fields', [
                            'formPrefix' => 'create',
                            'activity' => null,
                            'statusOptions' => $statusOptions,
                            'dayOptions' => $dayOptions,
                            'responsibleUserOptions' => $responsibleUserOptions,
                            'errorBag' => $errors->createAttendanceActivity,
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Kegiatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->createAttendanceActivity->any())
                document.getElementById('open-create-attendance-activity-modal')?.click();
            @endif

            @if ($errors->updateAttendanceActivity->any() && old('editing_attendance_activity_id'))
                const editActivityModalElement = document.getElementById('editAttendanceActivityModal{{ old('editing_attendance_activity_id') }}');

                if (editActivityModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(editActivityModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
