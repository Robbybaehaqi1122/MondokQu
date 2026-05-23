@php
    $selectedActivityId = old('attendance_activity_id', $session?->attendance_activity_id);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="{{ $formPrefix }}_attendance_activity_id" class="form-label">Kegiatan Absensi</label>
        <select
            id="{{ $formPrefix }}_attendance_activity_id"
            name="attendance_activity_id"
            class="form-select @if($errorBag->has('attendance_activity_id')) is-invalid @endif"
            required
        >
            <option value="">Pilih kegiatan</option>
            @foreach ($activityOptions as $activityOption)
                <option value="{{ $activityOption->id }}" @selected((string) $selectedActivityId === (string) $activityOption->id)>
                    {{ $activityOption->name }} - {{ $activityOption->timeRangeLabel() }}{{ $activityOption->status !== 'active' ? ' (Nonaktif)' : '' }}
                </option>
            @endforeach
        </select>
        @if ($errorBag->has('attendance_activity_id'))
            <div class="invalid-feedback">{{ $errorBag->first('attendance_activity_id') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="{{ $formPrefix }}_session_date" class="form-label">Tanggal Sesi</label>
        <input
            id="{{ $formPrefix }}_session_date"
            name="session_date"
            type="date"
            class="form-control @if($errorBag->has('session_date')) is-invalid @endif"
            value="{{ old('session_date', $session?->session_date?->toDateString() ?? now()->toDateString()) }}"
            required
        >
        @if ($errorBag->has('session_date'))
            <div class="invalid-feedback">{{ $errorBag->first('session_date') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="{{ $formPrefix }}_status" class="form-label">Status</label>
        <select id="{{ $formPrefix }}_status" name="status" class="form-select @if($errorBag->has('status')) is-invalid @endif" required>
            @foreach ($statusOptions as $statusOption)
                <option value="{{ $statusOption['value'] }}" @selected(old('status', $session?->status ?? 'draft') === $statusOption['value'])>
                    {{ $statusOption['label'] }}
                </option>
            @endforeach
        </select>
        @if ($errorBag->has('status'))
            <div class="invalid-feedback">{{ $errorBag->first('status') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label for="{{ $formPrefix }}_notes" class="form-label">Catatan</label>
        <textarea
            id="{{ $formPrefix }}_notes"
            name="notes"
            rows="3"
            class="form-control @if($errorBag->has('notes')) is-invalid @endif"
            placeholder="Opsional"
        >{{ old('notes', $session?->notes) }}</textarea>
        @if ($errorBag->has('notes'))
            <div class="invalid-feedback">{{ $errorBag->first('notes') }}</div>
        @endif
    </div>
</div>
