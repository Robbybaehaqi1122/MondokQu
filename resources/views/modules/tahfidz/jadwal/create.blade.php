<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Tambah Jadwal Setoran</h2>
            <div class="text-secondary mt-1">Buat jadwal setoran hafalan baru untuk musyrif/ustadz.</div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('tahfidz.jadwal.store') }}">
        @csrf

        <div class="row row-cards mb-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Jadwal</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="musyrif_id" class="form-label">Musyrif / Ustadz <span class="text-danger">*</span></label>
                            <select id="musyrif_id" name="musyrif_id" class="form-select @error('musyrif_id') is-invalid @enderror" required>
                                <option value="">Pilih Musyrif</option>
                                @foreach ($musyrifOptions as $musyrif)
                                    <option value="{{ $musyrif->id }}" @selected(old('musyrif_id') == $musyrif->id)>
                                        {{ $musyrif->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('musyrif_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="day_of_week" class="form-label">Hari <span class="text-danger">*</span></label>
                                <select id="day_of_week" name="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror" required>
                                    <option value="">Pilih Hari</option>
                                    @php
                                        $dayLabels = ['monday' => 'Senin', 'tuesday' => 'Selasa', 'wednesday' => 'Rabu', 'thursday' => 'Kamis', 'friday' => 'Jumat', 'saturday' => 'Sabtu', 'sunday' => 'Minggu'];
                                    @endphp
                                    @foreach ($daysOfWeek as $day)
                                        <option value="{{ $day }}" @selected(old('day_of_week') == $day)>
                                            {{ $dayLabels[$day] ?? ucfirst($day) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('day_of_week')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="start_time" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror"
                                    value="{{ old('start_time') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="end_time" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror"
                                    value="{{ old('end_time') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="max_santri" class="form-label">Maksimal Santri <span class="text-danger">*</span></label>
                                <input type="number" id="max_santri" name="max_santri" class="form-control @error('max_santri') is-invalid @enderror"
                                    value="{{ old('max_santri', 10) }}" min="1" max="255" required>
                                @error('max_santri')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="room_id" class="form-label">Ruangan</label>
                                <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                                    <option value="">Pilih Ruangan (opsional)</option>
                                    @foreach ($roomOptions as $room)
                                        <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="notes" class="form-label">Catatan</label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Catatan tambahan tentang jadwal ini...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i>
                Simpan Jadwal
            </button>
            <a href="{{ route('tahfidz.jadwal.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</x-app-layout>
