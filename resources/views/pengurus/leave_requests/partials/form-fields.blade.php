@php
    $oldBelongsToThisForm = $leaveRequest
        ? (string) old('editing_leave_request_id') === (string) $leaveRequest->id
        : old('editing_leave_request_id') === null;
    $selectedSantriId = $oldBelongsToThisForm ? old('santri_id', $leaveRequest?->santri_id) : $leaveRequest?->santri_id;
    $startDateValue = $oldBelongsToThisForm ? old('start_date', $leaveRequest?->start_date?->format('Y-m-d')) : $leaveRequest?->start_date?->format('Y-m-d');
    $endDateValue = $oldBelongsToThisForm ? old('end_date', $leaveRequest?->end_date?->format('Y-m-d')) : $leaveRequest?->end_date?->format('Y-m-d');
    $reasonValue = $oldBelongsToThisForm ? old('reason', $leaveRequest?->reason) : $leaveRequest?->reason;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="{{ $formPrefix }}santri_id" class="form-label fw-semibold">Santri</label>
        <select id="{{ $formPrefix }}santri_id" name="santri_id" class="form-select @if($errorBag->has('santri_id')) is-invalid @endif" required>
            <option value="">-- Pilih Santri --</option>
            @foreach ($santris as $santri)
                <option value="{{ $santri->id }}" @selected((string) $selectedSantriId === (string) $santri->id)>
                    {{ $santri->full_name }} ({{ $santri->nis }})
                </option>
            @endforeach
        </select>
        @if($errorBag->has('santri_id'))
            <div class="invalid-feedback">{{ $errorBag->first('santri_id') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="{{ $formPrefix }}start_date" class="form-label fw-semibold">Tanggal Mulai Izin</label>
        <input type="date" id="{{ $formPrefix }}start_date" name="start_date" class="form-control @if($errorBag->has('start_date')) is-invalid @endif" value="{{ $startDateValue }}" required>
        @if($errorBag->has('start_date'))
            <div class="invalid-feedback">{{ $errorBag->first('start_date') }}</div>
        @endif
    </div>

    <div class="col-md-6">
        <label for="{{ $formPrefix }}end_date" class="form-label fw-semibold">Tanggal Selesai Izin</label>
        <input type="date" id="{{ $formPrefix }}end_date" name="end_date" class="form-control @if($errorBag->has('end_date')) is-invalid @endif" value="{{ $endDateValue }}" required>
        @if($errorBag->has('end_date'))
            <div class="invalid-feedback">{{ $errorBag->first('end_date') }}</div>
        @endif
    </div>

    <div class="col-md-12">
        <label for="{{ $formPrefix }}reason" class="form-label fw-semibold">Alasan Izin</label>
        <textarea id="{{ $formPrefix }}reason" name="reason" class="form-control @if($errorBag->has('reason')) is-invalid @endif" rows="3" required>{{ $reasonValue }}</textarea>
        @if($errorBag->has('reason'))
            <div class="invalid-feedback">{{ $errorBag->first('reason') }}</div>
        @endif
    </div>
</div>
