@php
    $isCreateForm = $santriItem === null;
    $errorBagInstance = $errorsBag ?? $errors;
    $guardianOptions = $guardianUserOptions ?? collect();
    $roomOptions = $roomOptions ?? collect();
    $selectedGuardianUserIds = collect($selectedGuardianUserIds ?? old('guardian_user_ids', []))
        ->map(fn ($guardianUserId) => (int) $guardianUserId)
        ->all();
    $selectedRoomId = old('room_id', $santriItem?->room_id);
@endphp

{{-- Section 1: Data Santri --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Data Santri</h3>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="nis_{{ $santriFormId }}" class="form-label">NIS</label>
                <input
                    id="nis_{{ $santriFormId }}"
                    name="nis"
                    type="text"
                    class="form-control @if($errorBagInstance->has('nis')) is-invalid @endif"
                    value="{{ old('nis', $santriItem?->nis) }}"
                    required
                >
                @if ($errorBagInstance->has('nis'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('nis') }}</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="full_name_{{ $santriFormId }}" class="form-label">Nama Lengkap</label>
                <input
                    id="full_name_{{ $santriFormId }}"
                    name="full_name"
                    type="text"
                    class="form-control @if($errorBagInstance->has('full_name')) is-invalid @endif"
                    value="{{ old('full_name', $santriItem?->full_name) }}"
                    required
                >
                @if ($errorBagInstance->has('full_name'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('full_name') }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="gender_{{ $santriFormId }}" class="form-label">Jenis Kelamin</label>
                <select id="gender_{{ $santriFormId }}" name="gender" class="form-select form-select-pretty @if($errorBagInstance->has('gender')) is-invalid @endif" required>
                    @foreach ($genders as $gender)
                        <option value="{{ $gender['value'] }}" @selected(old('gender', $santriItem?->gender) === $gender['value'])>
                            {{ $gender['label'] }}
                        </option>
                    @endforeach
                </select>
                @if ($errorBagInstance->has('gender'))
                    <div class="invalid-feedback d-block">{{ $errorBagInstance->first('gender') }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="birth_place_{{ $santriFormId }}" class="form-label">Tempat Lahir</label>
                <input
                    id="birth_place_{{ $santriFormId }}"
                    name="birth_place"
                    type="text"
                    class="form-control @if($errorBagInstance->has('birth_place')) is-invalid @endif"
                    value="{{ old('birth_place', $santriItem?->birth_place) }}"
                    required
                >
                @if ($errorBagInstance->has('birth_place'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('birth_place') }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="birth_date_{{ $santriFormId }}" class="form-label">Tanggal Lahir</label>
                <input
                    id="birth_date_{{ $santriFormId }}"
                    name="birth_date"
                    type="date"
                    class="form-control @if($errorBagInstance->has('birth_date')) is-invalid @endif"
                    value="{{ old('birth_date', optional($santriItem?->birth_date)->format('Y-m-d')) }}"
                    required
                >
                @if ($errorBagInstance->has('birth_date'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('birth_date') }}</div>
                @endif
            </div>

            <div class="col-12">
                <label for="address_{{ $santriFormId }}" class="form-label">Alamat</label>
                <textarea id="address_{{ $santriFormId }}" name="address" class="form-control @if($errorBagInstance->has('address')) is-invalid @endif" rows="3" required>{{ old('address', $santriItem?->address) }}</textarea>
                @if ($errorBagInstance->has('address'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('address') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Section 2: Data Orang Tua --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Data Orang Tua</h3>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12">
                <h4 class="fs-5 text-primary mb-3">Ayah</h4>
            </div>
            <div class="col-md-6">
                <label for="father_name_{{ $santriFormId }}" class="form-label">Nama Ayah</label>
                <input
                    id="father_name_{{ $santriFormId }}"
                    name="father_name"
                    type="text"
                    class="form-control @if($errorBagInstance->has('father_name')) is-invalid @endif"
                    value="{{ old('father_name', $santriItem?->father_name) }}"
                    required
                >
                @if ($errorBagInstance->has('father_name'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('father_name') }}</div>
                @endif
            </div>
            <div class="col-md-6">
                <label for="father_phone_{{ $santriFormId }}" class="form-label">No. HP Ayah</label>
                <input
                    id="father_phone_{{ $santriFormId }}"
                    name="father_phone"
                    type="text"
                    class="form-control @if($errorBagInstance->has('father_phone')) is-invalid @endif"
                    value="{{ old('father_phone', $santriItem?->father_phone) }}"
                    placeholder="Contoh: 081234567890"
                    inputmode="numeric"
                    autocomplete="tel"
                    maxlength="20"
                    pattern="(?:\+62|62|0)[0-9]{8,15}"
                    title="Gunakan nomor yang diawali 0, 62, atau +62, lalu lanjutkan dengan angka. Contoh: 081234567890"
                    oninvalid="this.setCustomValidity('No. HP ayah harus diawali 0, 62, atau +62, lalu diikuti angka yang valid.')"
                    oninput="this.setCustomValidity('')"
                >
                @if ($errorBagInstance->has('father_phone'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('father_phone') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>
            <div class="col-md-6">
                <label for="father_education_{{ $santriFormId }}" class="form-label">Pendidikan Ayah</label>
                <input
                    id="father_education_{{ $santriFormId }}"
                    name="father_education"
                    type="text"
                    class="form-control @if($errorBagInstance->has('father_education')) is-invalid @endif"
                    value="{{ old('father_education', $santriItem?->father_education) }}"
                    maxlength="100"
                >
                @if ($errorBagInstance->has('father_education'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('father_education') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>
            <div class="col-md-6">
                <label for="father_job_{{ $santriFormId }}" class="form-label">Pekerjaan Ayah</label>
                <input
                    id="father_job_{{ $santriFormId }}"
                    name="father_job"
                    type="text"
                    class="form-control @if($errorBagInstance->has('father_job')) is-invalid @endif"
                    value="{{ old('father_job', $santriItem?->father_job) }}"
                    maxlength="100"
                >
                @if ($errorBagInstance->has('father_job'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('father_job') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>
        </div>

        <hr class="my-3">

        <div class="row">
            <div class="col-12">
                <h4 class="fs-5 text-success mb-3">Ibu</h4>
            </div>
            <div class="col-md-6">
                <label for="mother_name_{{ $santriFormId }}" class="form-label">Nama Ibu</label>
                <input
                    id="mother_name_{{ $santriFormId }}"
                    name="mother_name"
                    type="text"
                    class="form-control @if($errorBagInstance->has('mother_name')) is-invalid @endif"
                    value="{{ old('mother_name', $santriItem?->mother_name) }}"
                    required
                >
                @if ($errorBagInstance->has('mother_name'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('mother_name') }}</div>
                @endif
            </div>
            <div class="col-md-6">
                <label for="mother_phone_{{ $santriFormId }}" class="form-label">No. HP Ibu</label>
                <input
                    id="mother_phone_{{ $santriFormId }}"
                    name="mother_phone"
                    type="text"
                    class="form-control @if($errorBagInstance->has('mother_phone')) is-invalid @endif"
                    value="{{ old('mother_phone', $santriItem?->mother_phone) }}"
                    placeholder="Contoh: 081234567890"
                    inputmode="numeric"
                    autocomplete="tel"
                    maxlength="20"
                    pattern="(?:\+62|62|0)[0-9]{8,15}"
                    title="Gunakan nomor yang diawali 0, 62, atau +62, lalu lanjutkan dengan angka. Contoh: 081234567890"
                    oninvalid="this.setCustomValidity('No. HP ibu harus diawali 0, 62, atau +62, lalu diikuti angka yang valid.')"
                    oninput="this.setCustomValidity('')"
                >
                @if ($errorBagInstance->has('mother_phone'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('mother_phone') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>
            <div class="col-md-6">
                <label for="mother_education_{{ $santriFormId }}" class="form-label">Pendidikan Ibu</label>
                <input
                    id="mother_education_{{ $santriFormId }}"
                    name="mother_education"
                    type="text"
                    class="form-control @if($errorBagInstance->has('mother_education')) is-invalid @endif"
                    value="{{ old('mother_education', $santriItem?->mother_education) }}"
                    maxlength="100"
                >
                @if ($errorBagInstance->has('mother_education'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('mother_education') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>
            <div class="col-md-6">
                <label for="mother_job_{{ $santriFormId }}" class="form-label">Pekerjaan Ibu</label>
                <input
                    id="mother_job_{{ $santriFormId }}"
                    name="mother_job"
                    type="text"
                    class="form-control @if($errorBagInstance->has('mother_job')) is-invalid @endif"
                    value="{{ old('mother_job', $santriItem?->mother_job) }}"
                    maxlength="100"
                >
                @if ($errorBagInstance->has('mother_job'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('mother_job') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Section 3: Data Wali & Informasi Lain --}}
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Data Wali &amp; Informasi Lain</h3>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="guardian_name_{{ $santriFormId }}" class="form-label">Nama Wali / Penanggung Jawab</label>
                <input
                    id="guardian_name_{{ $santriFormId }}"
                    name="guardian_name"
                    type="text"
                    class="form-control @if($errorBagInstance->has('guardian_name')) is-invalid @endif"
                    value="{{ old('guardian_name', $santriItem?->guardian_name) }}"
                >
                @if ($errorBagInstance->has('guardian_name'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('guardian_name') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="guardian_phone_number_{{ $santriFormId }}" class="form-label">No. HP Wali / Penanggung Jawab</label>
                <input
                    id="guardian_phone_number_{{ $santriFormId }}"
                    name="guardian_phone_number"
                    type="text"
                    class="form-control @if($errorBagInstance->has('guardian_phone_number')) is-invalid @endif"
                    value="{{ old('guardian_phone_number', $santriItem?->guardian_phone_number) }}"
                    placeholder="Contoh: 081234567890"
                    inputmode="numeric"
                    autocomplete="tel"
                    minlength="10"
                    maxlength="20"
                    pattern="(?:\+62|62|0)[0-9]{8,15}"
                    title="Gunakan nomor yang diawali 0, 62, atau +62, lalu lanjutkan dengan angka. Contoh: 081234567890"
                    oninvalid="this.setCustomValidity('No. HP wali / penanggung jawab harus diawali 0, 62, atau +62, lalu diikuti angka yang valid.')"
                    oninput="this.setCustomValidity('')"
                >
                @if ($errorBagInstance->has('guardian_phone_number'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('guardian_phone_number') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="guardian_relation_{{ $santriFormId }}" class="form-label">Hubungan dengan Santri</label>
                <input
                    id="guardian_relation_{{ $santriFormId }}"
                    name="guardian_relation"
                    type="text"
                    class="form-control @if($errorBagInstance->has('guardian_relation')) is-invalid @endif"
                    value="{{ old('guardian_relation', $santriItem?->guardian_relation) }}"
                    maxlength="50"
                    placeholder="Contoh: Paman, Nenek, Kakak"
                >
                @if ($errorBagInstance->has('guardian_relation'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('guardian_relation') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="guardian_address_{{ $santriFormId }}" class="form-label">Alamat Wali</label>
                <textarea
                    id="guardian_address_{{ $santriFormId }}"
                    name="guardian_address"
                    class="form-control @if($errorBagInstance->has('guardian_address')) is-invalid @endif"
                    rows="2"
                >{{ old('guardian_address', $santriItem?->guardian_address) }}</textarea>
                @if ($errorBagInstance->has('guardian_address'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('guardian_address') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>

            <div class="col-md-6">
                <label for="emergency_contact_{{ $santriFormId }}" class="form-label">Kontak Darurat</label>
                <input
                    id="emergency_contact_{{ $santriFormId }}"
                    name="emergency_contact"
                    type="text"
                    class="form-control @if($errorBagInstance->has('emergency_contact')) is-invalid @endif"
                    value="{{ old('emergency_contact', $santriItem?->emergency_contact) }}"
                    placeholder="Contoh: 081234567890"
                    inputmode="numeric"
                    autocomplete="tel"
                    minlength="10"
                    maxlength="20"
                    pattern="(?:\+62|62|0)[0-9]{8,15}"
                    title="Gunakan nomor yang diawali 0, 62, atau +62, lalu lanjutkan dengan angka. Contoh: 081234567890"
                    oninvalid="this.setCustomValidity('Kontak darurat harus diawali 0, 62, atau +62, lalu diikuti angka yang valid.')"
                    oninput="this.setCustomValidity('')"
                    required
                >
                @if ($errorBagInstance->has('emergency_contact'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('emergency_contact') }}</div>
                @else
                    <div class="form-hint mt-2">Nomor keluarga terdekat.</div>
                @endif
            </div>

            <div class="col-12">
                <label class="form-label">Akun Wali Portal</label>
                @if ($guardianOptions->isNotEmpty())
                    <div class="list-group list-group-flush border rounded">
                        @foreach ($guardianOptions as $guardianUser)
                            <label class="list-group-item d-flex align-items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="guardian_user_ids[]"
                                    value="{{ $guardianUser->id }}"
                                    class="form-check-input mt-1 @if($errorBagInstance->has('guardian_user_ids')) is-invalid @endif"
                                    @checked(in_array((int) $guardianUser->id, $selectedGuardianUserIds, true))
                                >
                                <span class="flex-fill">
                                    <span class="fw-semibold d-block">{{ $guardianUser->name }}</span>
                                    <span class="text-secondary small">{{ '@'.$guardianUser->username }} &middot; {{ $guardianUser->email }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @if ($errorBagInstance->has('guardian_user_ids'))
                        <div class="invalid-feedback d-block">{{ $errorBagInstance->first('guardian_user_ids') }}</div>
                    @else
                        <div class="form-hint mt-2">Opsional</div>
                    @endif
                @else
                    <div class="alert alert-secondary mb-0">
                        Belum ada user role Wali Santri pada tenant ini.
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="entry_date_{{ $santriFormId }}" class="form-label">Tanggal Masuk</label>
                <input
                    id="entry_date_{{ $santriFormId }}"
                    name="entry_date"
                    type="date"
                    class="form-control @if($errorBagInstance->has('entry_date')) is-invalid @endif"
                    value="{{ old('entry_date', optional($santriItem?->entry_date)->format('Y-m-d')) }}"
                    required
                >
                @if ($errorBagInstance->has('entry_date'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('entry_date') }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="entry_year_{{ $santriFormId }}" class="form-label">Angkatan / Tahun Masuk</label>
                <input
                    id="entry_year_{{ $santriFormId }}"
                    name="entry_year"
                    type="number"
                    class="form-control @if($errorBagInstance->has('entry_year')) is-invalid @endif"
                    value="{{ old('entry_year', $santriItem?->entry_year ?? optional($santriItem?->entry_date)->format('Y')) }}"
                    min="1900"
                    max="{{ now()->year }}"
                    placeholder="Contoh: {{ now()->year }}"
                    required
                >
                @if ($errorBagInstance->has('entry_year'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('entry_year') }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="room_id_{{ $santriFormId }}" class="form-label">Kamar / Asrama</label>
                @if ($roomOptions->isNotEmpty())
                    <select
                        id="room_id_{{ $santriFormId }}"
                        name="room_id"
                        class="form-select form-select-pretty @if($errorBagInstance->has('room_id') || $errorBagInstance->has('room_name')) is-invalid @endif"
                        required
                    >
                        <option value="">Pilih kamar</option>
                        @foreach ($roomOptions as $roomOption)
                            <option value="{{ $roomOption->id }}" @selected((string) $selectedRoomId === (string) $roomOption->id)>
                                {{ $roomOption->name }}{{ $roomOption->status !== 'active' ? ' (Nonaktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if ($errorBagInstance->has('room_id'))
                        <div class="invalid-feedback d-block">{{ $errorBagInstance->first('room_id') }}</div>
                    @elseif ($errorBagInstance->has('room_name'))
                        <div class="invalid-feedback d-block">{{ $errorBagInstance->first('room_name') }}</div>
                    @else
                        <div class="form-hint mt-2">Pilih dari master kamar.</div>
                    @endif
                @else
                    <select
                        id="room_id_{{ $santriFormId }}"
                        name="room_id"
                        class="form-select form-select-pretty @if($errorBagInstance->has('room_id') || $errorBagInstance->has('room_name')) is-invalid @endif"
                        disabled
                        required
                    >
                        <option value="">Belum ada kamar</option>
                    </select>
                    @if ($errorBagInstance->has('room_id'))
                        <div class="invalid-feedback d-block">{{ $errorBagInstance->first('room_id') }}</div>
                    @elseif ($errorBagInstance->has('room_name'))
                        <div class="invalid-feedback d-block">{{ $errorBagInstance->first('room_name') }}</div>
                    @else
                        <div class="form-hint mt-2">
                            Buat kamar terlebih dahulu di
                            <a href="{{ route('rooms.index') }}" class="link-primary">Manajemen Kamar</a>.
                        </div>
                    @endif
                @endif
            </div>

            <div class="col-md-6">
                <label for="status_{{ $santriFormId }}" class="form-label">Status</label>
                <select id="status_{{ $santriFormId }}" name="status" class="form-select form-select-pretty @if($errorBagInstance->has('status')) is-invalid @endif" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status['value'] }}" @selected(old('status', $santriItem?->status ?? 'active') === $status['value'])>
                            {{ $status['label'] }}
                        </option>
                    @endforeach
                </select>
                @if ($errorBagInstance->has('status'))
                    <div class="invalid-feedback d-block">{{ $errorBagInstance->first('status') }}</div>
                @endif
            </div>

            <div class="col-12">
                <label for="notes_{{ $santriFormId }}" class="form-label">Catatan Singkat</label>
                <textarea
                    id="notes_{{ $santriFormId }}"
                    name="notes"
                    class="form-control @if($errorBagInstance->has('notes')) is-invalid @endif"
                    rows="3"
                    maxlength="1000"
                    placeholder="Opsional. Catatan kesehatan, kebutuhan khusus, dll."
                >{{ old('notes', $santriItem?->notes) }}</textarea>
                @if ($errorBagInstance->has('notes'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('notes') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional</div>
                @endif
            </div>

            <div class="col-12">
                <label for="photo_{{ $santriFormId }}" class="form-label">Foto</label>
                @if ($santriItem?->photoUrl())
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $santriItem->photoUrl() }}" alt="Foto {{ $santriItem->full_name }}" class="user-inline-avatar" loading="lazy">
                        <div class="text-secondary small">Foto saat ini</div>
                    </div>

                    <div class="form-check mb-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            value="1"
                            id="delete_photo_{{ $santriFormId }}"
                            name="delete_photo"
                            @checked(old('delete_photo'))
                        >
                        <label class="form-check-label" for="delete_photo_{{ $santriFormId }}">
                            Hapus foto lama tanpa mengunggah foto baru
                        </label>
                    </div>
                @endif

                <input
                    id="photo_{{ $santriFormId }}"
                    name="photo"
                    type="file"
                    class="form-control @if($errorBagInstance->has('photo')) is-invalid @endif"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >
                @if ($errorBagInstance->has('photo'))
                    <div class="invalid-feedback">{{ $errorBagInstance->first('photo') }}</div>
                @else
                    <div class="form-hint mt-2">Opsional. JPG/PNG/WEBP, maks 2 MB.</div>
                @endif
            </div>
        </div>
    </div>
</div>
