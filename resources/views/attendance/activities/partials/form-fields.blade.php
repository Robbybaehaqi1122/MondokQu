@php
    $selectedDays = old('active_days', $activity?->active_days ?? [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
    ]);
    $selectedDays = is_array($selectedDays) ? $selectedDays : [];
    $selectedResponsibleUserId = old('responsible_user_id', $activity?->responsible_user_id);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="{{ $formPrefix }}_name" class="form-label">Nama Kegiatan</label>
        <input
            id="{{ $formPrefix }}_name"
            name="name"
            type="text"
            class="form-control @if($errorBag->has('name')) is-invalid @endif"
            value="{{ old('name', $activity?->name) }}"
            placeholder="Contoh: Halaqah Pagi"
            required
        >
        @if ($errorBag->has('name'))
            <div class="invalid-feedback">{{ $errorBag->first('name') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="{{ $formPrefix }}_start_time" class="form-label">Jam Mulai</label>
        <input
            id="{{ $formPrefix }}_start_time"
            name="start_time"
            type="time"
            class="form-control @if($errorBag->has('start_time')) is-invalid @endif"
            value="{{ old('start_time', $activity?->timeInputValue($activity?->start_time)) }}"
            required
        >
        @if ($errorBag->has('start_time'))
            <div class="invalid-feedback">{{ $errorBag->first('start_time') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="{{ $formPrefix }}_end_time" class="form-label">Jam Selesai</label>
        <input
            id="{{ $formPrefix }}_end_time"
            name="end_time"
            type="time"
            class="form-control @if($errorBag->has('end_time')) is-invalid @endif"
            value="{{ old('end_time', $activity?->timeInputValue($activity?->end_time)) }}"
        >
        @if ($errorBag->has('end_time'))
            <div class="invalid-feedback">{{ $errorBag->first('end_time') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="{{ $formPrefix }}_responsible_user_id" class="form-label">Penanggung Jawab</label>
        <select
            id="{{ $formPrefix }}_responsible_user_id"
            name="responsible_user_id"
            class="form-select @if($errorBag->has('responsible_user_id')) is-invalid @endif"
        >
            <option value="">Tidak ditentukan</option>
            @foreach ($responsibleUserOptions as $responsibleUser)
                <option value="{{ $responsibleUser->id }}" @selected((string) $selectedResponsibleUserId === (string) $responsibleUser->id)>
                    {{ $responsibleUser->name }}{{ $responsibleUser->tenant?->name ? ' - '.$responsibleUser->tenant->name : '' }}
                </option>
            @endforeach
        </select>
        @if ($errorBag->has('responsible_user_id'))
            <div class="invalid-feedback">{{ $errorBag->first('responsible_user_id') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="{{ $formPrefix }}_status" class="form-label">Status</label>
        <select id="{{ $formPrefix }}_status" name="status" class="form-select @if($errorBag->has('status')) is-invalid @endif" required>
            @foreach ($statusOptions as $statusOption)
                <option value="{{ $statusOption['value'] }}" @selected(old('status', $activity?->status ?? 'active') === $statusOption['value'])>
                    {{ $statusOption['label'] }}
                </option>
            @endforeach
        </select>
        @if ($errorBag->has('status'))
            <div class="invalid-feedback">{{ $errorBag->first('status') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">Hari Aktif</label>
        <div class="attendance-day-grid @if($errorBag->has('active_days') || $errorBag->has('active_days.*')) is-invalid @endif">
            @foreach ($dayOptions as $dayOption)
                <label class="attendance-day-option">
                    <input
                        type="checkbox"
                        name="active_days[]"
                        value="{{ $dayOption['value'] }}"
                        class="attendance-day-input"
                        @checked(in_array($dayOption['value'], $selectedDays, true))
                    >
                    <span class="attendance-day-label">{{ $dayOption['label'] }}</span>
                    <span class="attendance-day-check" aria-hidden="true">
                        <i class="ti ti-check"></i>
                    </span>
                </label>
            @endforeach
        </div>
        @if ($errorBag->has('active_days'))
            <div class="invalid-feedback d-block">{{ $errorBag->first('active_days') }}</div>
        @elseif ($errorBag->has('active_days.*'))
            <div class="invalid-feedback d-block">{{ $errorBag->first('active_days.*') }}</div>
        @endif
    </div>

    <div class="col-12">
        <label for="{{ $formPrefix }}_description" class="form-label">Catatan</label>
        <textarea
            id="{{ $formPrefix }}_description"
            name="description"
            rows="3"
            class="form-control @if($errorBag->has('description')) is-invalid @endif"
            placeholder="Opsional"
        >{{ old('description', $activity?->description) }}</textarea>
        @if ($errorBag->has('description'))
            <div class="invalid-feedback">{{ $errorBag->first('description') }}</div>
        @endif
    </div>
</div>
