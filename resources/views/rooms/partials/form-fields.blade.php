<div class="row g-3">
    <div class="col-md-6">
        <label for="{{ $formPrefix }}_name" class="form-label">Nama Kamar</label>
        <input
            id="{{ $formPrefix }}_name"
            name="name"
            type="text"
            class="form-control @if($errorBag->has('name')) is-invalid @endif"
            value="{{ old('name', $room?->name) }}"
            required
        >
        @if ($errorBag->has('name'))
            <div class="invalid-feedback">{{ $errorBag->first('name') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="{{ $formPrefix }}_capacity" class="form-label">Kapasitas</label>
        <input
            id="{{ $formPrefix }}_capacity"
            name="capacity"
            type="number"
            min="1"
            max="1000"
            class="form-control @if($errorBag->has('capacity')) is-invalid @endif"
            value="{{ old('capacity', $room?->capacity) }}"
            placeholder="Opsional"
        >
        @if ($errorBag->has('capacity'))
            <div class="invalid-feedback">{{ $errorBag->first('capacity') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="{{ $formPrefix }}_status" class="form-label">Status</label>
        <select id="{{ $formPrefix }}_status" name="status" class="form-select @if($errorBag->has('status')) is-invalid @endif" required>
            @foreach ($statusOptions as $statusOption)
                <option value="{{ $statusOption['value'] }}" @selected(old('status', $room?->status ?? 'active') === $statusOption['value'])>
                    {{ $statusOption['label'] }}
                </option>
            @endforeach
        </select>
        @if ($errorBag->has('status'))
            <div class="invalid-feedback">{{ $errorBag->first('status') }}</div>
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
        >{{ old('description', $room?->description) }}</textarea>
        @if ($errorBag->has('description'))
            <div class="invalid-feedback">{{ $errorBag->first('description') }}</div>
        @endif
    </div>
</div>
