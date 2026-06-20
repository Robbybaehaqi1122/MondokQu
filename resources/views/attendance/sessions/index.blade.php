<x-app-layout>
    @php
        $statusBadgeClasses = [
            'draft' => 'bg-secondary-lt text-secondary',
            'open' => 'bg-primary-lt text-primary',
            'completed' => 'bg-success-lt text-success',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Sesi Absensi Harian</h2>
            <div class="text-secondary mt-1">Buat sesi harian dari master kegiatan sebelum input absensi santri.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Sesi</div>
                <div class="fs-2 fw-bold">{{ number_format($sessionStats['total']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Draft</div>
                <div class="fs-2 fw-bold">{{ number_format($sessionStats['draft']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Dibuka</div>
                <div class="fs-2 fw-bold">{{ number_format($sessionStats['open']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Hari Ini</div>
                <div class="fs-2 fw-bold">{{ number_format($sessionStats['today']) }}</div>
            </div>
        </div>
    </div>

    @if ($activityOptions->isEmpty())
        <div class="alert alert-warning">
            Master kegiatan absensi belum tersedia. Buat kegiatan terlebih dahulu sebelum membuat sesi harian.
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                <div>
                    <h3 class="card-title">Daftar Sesi</h3>
                    <div class="text-secondary small mt-2">Menampilkan {{ $sessions->total() }} sesi berdasarkan filter aktif.</div>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="open-create-attendance-session-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#createAttendanceSessionModal"
                    @disabled($activityOptions->isEmpty())
                >
                    <i class="ti ti-plus me-1"></i>
                    Tambah Sesi
                </button>
            </div>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('attendance.sessions.index') }}" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label for="activity" class="form-label">Kegiatan</label>
                    <select id="activity" name="activity" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($activityOptions as $activityOption)
                            <option value="{{ $activityOption->id }}" @selected($filters['activity'] === (string) $activityOption->id)>
                                {{ $activityOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-2">
                    <label for="date_from" class="form-label">Dari Tanggal</label>
                    <input id="date_from" name="date_from" type="date" class="form-control" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-6 col-lg-2">
                    <label for="date_to" class="form-label">Sampai Tanggal</label>
                    <input id="date_to" name="date_to" type="date" class="form-control" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-6 col-lg-2">
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
                <div class="col-md-6 col-lg-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-filter me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('attendance.sessions.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Sesi</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Catatan</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $session->activity?->name ?? '-' }}</div>
                                <div class="text-secondary small">{{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $session->activity?->timeRangeLabel() ?? '-' }}</div>
                                <div class="text-secondary small">{{ $session->activity?->activeDayLabels() ?: '-' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadgeClasses[$session->status] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $session->statusLabel() }}
                                </span>
                                <div class="text-secondary small mt-1">
                                    {{ number_format((int) $session->records_count) }} santri terisi
                                    @if ((int) $session->issue_records_count > 0)
                                        &bull; {{ number_format((int) $session->issue_records_count) }} perlu perhatian
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($session->creator)
                                    <div>{{ $session->creator->name }}</div>
                                    <div class="text-secondary small">{{ '@'.$session->creator->username }}</div>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary small">{{ $session->notes ?: '-' }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('attendance.sessions.records.edit', $session) }}" class="btn btn-outline-primary btn-sm btn-icon" aria-label="Input absensi">
                                        <i class="ti ti-clipboard-check"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editAttendanceSessionModal{{ $session->id }}" aria-label="Edit sesi">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('attendance.sessions.destroy', $session) }}" onsubmit="return confirm('Hapus sesi absensi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-icon" aria-label="Hapus sesi">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="modal modal-blur fade" id="editAttendanceSessionModal{{ $session->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('attendance.sessions.update', $session) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="editing_attendance_session_id" value="{{ $session->id }}">

                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title">Edit Sesi Absensi</h5>
                                                        <div class="text-secondary small mt-1">{{ $session->activity?->name }} - {{ $session->session_date?->translatedFormat('d M Y') }}</div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @include('attendance.sessions.partials.form-fields', [
                                                        'formPrefix' => 'edit_'.$session->id,
                                                        'session' => $session,
                                                        'activityOptions' => $activityOptions,
                                                        'statusOptions' => $statusOptions,
                                                        'errorBag' => $errors->updateAttendanceSession,
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
                            <td colspan="6" class="text-secondary">Belum ada sesi absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sessions->hasPages())
            <div class="card-footer">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>

    <div class="modal modal-blur fade" id="createAttendanceSessionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('attendance.sessions.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Sesi Absensi</h5>
                            <div class="text-secondary small mt-1">Sesi dibuat untuk satu kegiatan pada satu tanggal.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @include('attendance.sessions.partials.form-fields', [
                            'formPrefix' => 'create',
                            'session' => null,
                            'activityOptions' => $activityOptions,
                            'statusOptions' => $statusOptions,
                            'errorBag' => $errors->createAttendanceSession,
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Sesi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->createAttendanceSession->any())
                document.getElementById('open-create-attendance-session-modal')?.click();
            @endif

            @if ($errors->updateAttendanceSession->any() && old('editing_attendance_session_id'))
                const editSessionModalElement = document.getElementById('editAttendanceSessionModal{{ old('editing_attendance_session_id') }}');

                if (editSessionModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(editSessionModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
