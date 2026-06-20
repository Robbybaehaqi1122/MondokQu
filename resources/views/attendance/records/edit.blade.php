<x-app-layout>
    @php
        $statusBadgeClasses = [
            'draft' => 'bg-secondary-lt text-secondary',
            'open' => 'bg-primary-lt text-primary',
            'completed' => 'bg-success-lt text-success',
        ];
        $recordBadgeClasses = [
            'present' => 'bg-success-lt text-success',
            'permission' => 'bg-azure-lt text-azure',
            'sick' => 'bg-warning-lt text-warning',
            'absent' => 'bg-danger-lt text-danger',
            'late' => 'bg-orange-lt text-orange',
        ];
        $statusActionIcons = [
            'present' => 'ti-check',
            'permission' => 'ti-calendar-check',
            'sick' => 'ti-thermometer',
            'absent' => 'ti-user-x',
            'late' => 'ti-clock-exclamation',
        ];
        $roomFilterOptions = $activeSantris
            ->map(fn ($santri) => $santri->displayRoomName('Belum diatur'))
            ->unique()
            ->sort()
            ->values();
        $oldRecords = collect(old('records', []))->keyBy(fn ($record) => (int) data_get($record, 'santri_id'));
    @endphp

    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Input Absensi Santri</h2>
                <div class="text-secondary mt-1">
                    {{ $session->activity?->name ?? '-' }} &bull; {{ $session->session_date?->translatedFormat('d M Y') ?? '-' }}
                </div>
            </div>
            <a href="{{ route('attendance.sessions.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-2">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Santri Aktif</div>
                <div class="fs-2 fw-bold">{{ number_format($activeSantris->count()) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-2">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Terisi</div>
                <div class="fs-2 fw-bold">{{ number_format($recordStats['recorded']) }}</div>
            </div>
        </div>
        @foreach ($statusOptions as $statusOption)
            <div class="col-sm-6 col-lg-2">
                <div class="card card-body">
                    <div class="text-uppercase text-secondary small">{{ $statusOption['label'] }}</div>
                    <div class="fs-2 fw-bold">{{ number_format($recordStats[$statusOption['value']] ?? 0) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-lg-between gap-3">
                <div>
                    <div class="fw-semibold">{{ $session->activity?->name ?? '-' }}</div>
                    <div class="text-secondary small mt-1">
                        Jadwal {{ $session->activity?->timeRangeLabel() ?? '-' }}
                        &bull; {{ $session->activity?->activeDayLabels() ?: '-' }}
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-start">
                    <span class="badge {{ $statusBadgeClasses[$session->status] ?? 'bg-secondary-lt text-secondary' }}">
                        {{ $session->statusLabel() }}
                    </span>
                    @if ($session->activity?->responsibleUser)
                        <span class="badge bg-blue-lt text-blue">
                            {{ $session->activity->responsibleUser->name }}
                        </span>
                    @endif
                </div>
            </div>
            @if ($session->notes)
                <div class="text-secondary mt-3">{{ $session->notes }}</div>
            @endif
        </div>
    </div>

    @if (! $canEditRecords)
        <div class="alert alert-info">
            Sesi ini sudah selesai. Data absensi ditampilkan sebagai arsip dan tidak bisa diubah dari halaman ini.
        </div>
    @endif

    @if ($errors->attendanceRecords->any())
        <div class="alert alert-danger">
            {{ $errors->attendanceRecords->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('attendance.sessions.records.update', $session) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Daftar Santri</h3>
                    <div class="text-secondary small mt-2">Default santri aktif adalah Hadir, santri dengan izin aktif akan tampil sebagai Izin.</div>
                </div>
            </div>

            <div class="card-body border-bottom attendance-record-toolbar" data-attendance-record-tools>
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label for="attendance-record-search" class="form-label">Cari Santri</label>
                        <div class="input-icon">
                            <span class="input-icon-addon">
                                <i class="ti ti-search"></i>
                            </span>
                            <input
                                id="attendance-record-search"
                                type="search"
                                class="form-control"
                                placeholder="Nama atau NIS"
                                autocomplete="off"
                                data-attendance-search-input
                            >
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label for="attendance-record-room" class="form-label">Kamar</label>
                        <select id="attendance-record-room" class="form-select" data-attendance-room-filter>
                            <option value="">Semua kamar</option>
                            @foreach ($roomFilterOptions as $roomOption)
                                <option value="{{ strtolower($roomOption) }}">{{ $roomOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-5">
                        <label class="form-label">Aksi Cepat</label>
                        <div class="attendance-quick-actions">
                            @foreach ($statusOptions as $statusOption)
                                @php
                                    $isPresentStatus = $statusOption['value'] === $defaultStatus;
                                    $iconClass = $statusActionIcons[$statusOption['value']] ?? 'ti-circle';
                                @endphp
                                <button
                                    type="button"
                                    class="btn {{ $isPresentStatus ? 'btn-outline-success' : 'btn-outline-secondary' }}"
                                    data-attendance-bulk-status="{{ $statusOption['value'] }}"
                                    @disabled(! $canEditRecords)
                                >
                                    <i class="ti {{ $iconClass }} me-1"></i>
                                    {{ $isPresentStatus ? 'Tandai Semua Hadir' : $statusOption['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="attendance-record-toolbar-meta">
                    <span class="badge bg-secondary-lt text-secondary" data-attendance-visible-count>{{ number_format($activeSantris->count()) }} tampil</span>
                    <button type="button" class="btn btn-link px-0" data-attendance-reset-filter>
                        <i class="ti ti-refresh me-1"></i>
                        Reset Filter
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Santri</th>
                            <th>Kamar</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Terakhir Diinput</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeSantris as $index => $santri)
                            @php
                                $record = $recordsBySantri->get($santri->id);
                                $activeLeaveRequest = $activeLeaveRequestsBySantri->get($santri->id);
                                $oldRecord = $oldRecords->get($santri->id, []);
                                $roomName = $santri->displayRoomName('Belum diatur');
                                $searchKeywords = strtolower($santri->full_name.' '.$santri->nis.' '.$roomName);
                                $roomKey = strtolower($roomName);
                                $defaultSantriStatus = $activeLeaveRequest ? $permissionStatus : $defaultStatus;
                                $selectedStatus = data_get($oldRecord, 'status', $record?->status ?? $defaultSantriStatus);
                                $noteValue = data_get($oldRecord, 'notes', $record?->notes);
                            @endphp
                            <tr data-attendance-row data-attendance-search="{{ $searchKeywords }}" data-attendance-room="{{ $roomKey }}">
                                <td>
                                    <input type="hidden" name="records[{{ $index }}][santri_id]" value="{{ $santri->id }}">
                                    <div class="fw-semibold">{{ $santri->full_name }}</div>
                                    <div class="text-secondary small">NIS {{ $santri->nis }}</div>
                                    @if ($activeLeaveRequest)
                                        <div class="small text-azure mt-1">
                                            <i class="ti ti-calendar-check me-1"></i>
                                            Izin {{ $activeLeaveRequest->start_date?->translatedFormat('d M') }} - {{ $activeLeaveRequest->end_date?->translatedFormat('d M Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $roomName }}</td>
                                <td>
                                    <select name="records[{{ $index }}][status]" class="form-select" data-attendance-status-select @disabled(! $canEditRecords)>
                                        @foreach ($statusOptions as $statusOption)
                                            <option value="{{ $statusOption['value'] }}" @selected($selectedStatus === $statusOption['value'])>
                                                {{ $statusOption['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($record)
                                        <span class="badge mt-2 {{ $recordBadgeClasses[$record->status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $record->statusLabel() }}
                                        </span>
                                    @elseif ($activeLeaveRequest)
                                        <span class="badge mt-2 bg-azure-lt text-azure">
                                            <i class="ti ti-calendar-check me-1"></i>
                                            Izin aktif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        name="records[{{ $index }}][notes]"
                                        class="form-control"
                                        value="{{ $noteValue }}"
                                        placeholder="Opsional"
                                        @disabled(! $canEditRecords)
                                    >
                                </td>
                                <td>
                                    @if ($record?->recorded_at)
                                        <div>{{ $record->recorded_at->translatedFormat('d M Y H:i') }}</div>
                                        <div class="text-secondary small">{{ $record->recorder?->name ?? '-' }}</div>
                                    @else
                                        <span class="text-secondary small">Belum pernah diinput</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-secondary">Belum ada santri aktif untuk tenant ini.</td>
                            </tr>
                        @endforelse
                        @if ($activeSantris->isNotEmpty())
                            <tr data-attendance-empty-row hidden>
                                <td colspan="5" class="text-secondary text-center py-4">Tidak ada santri sesuai filter.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex flex-wrap justify-content-end gap-2">
                <a href="{{ route('attendance.sessions.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" @disabled(! $canEditRecords || $activeSantris->isEmpty())>
                    <i class="ti ti-device-floppy me-1"></i>
                    Simpan Absensi
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
