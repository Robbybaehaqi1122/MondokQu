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
            <h2 class="page-title">Edit Pengajuan Izin Santri</h2>
            <div class="text-secondary mt-1">Mengedit: {{ $leaveRequest->santri->full_name }}</div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title">Edit Pengajuan Izin</h3>
                </div>
                <div>
                    <a href="{{ route('pengurus.izin.index') }}" class="btn btn-outline-secondary">Kembali ke Daftar</a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('pengurus.izin.update', $leaveRequest) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="editing_leave_request_id" value="{{ $leaveRequest->id }}">

                <div class="mb-3">
                    <label for="edit_santri_id" class="form-label fw-semibold">Santri</label>
                    <select id="edit_santri_id" name="santri_id" class="form-select @if($errors->updateLeaveRequest->has('santri_id')) is-invalid @endif" required>
                        <option value="">-- Pilih Santri --</option>
                        @foreach ($santris as $santri)
                            <option value="{{ $santri->id }}" @selected((string) old('santri_id', $leaveRequest->santri_id) === (string) $santri->id)>
                                {{ $santri->full_name }} ({{ $santri->nis }})
                            </option>
                        @endforeach
                    </select>
                    @if($errors->updateLeaveRequest->has('santri_id'))
                        <div class="invalid-feedback">{{ $errors->updateLeaveRequest->first('santri_id') }}</div>
                    @endif
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="edit_start_date" class="form-label fw-semibold">Tanggal Mulai Izin</label>
                        <input type="date" id="edit_start_date" name="start_date" class="form-control @if($errors->updateLeaveRequest->has('start_date')) is-invalid @endif" value="{{ old('start_date', $leaveRequest->start_date?->format('Y-m-d')) }}" required>
                        @if($errors->updateLeaveRequest->has('start_date'))
                            <div class="invalid-feedback">{{ $errors->updateLeaveRequest->first('start_date') }}</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="edit_end_date" class="form-label fw-semibold">Tanggal Selesai Izin</label>
                        <input type="date" id="edit_end_date" name="end_date" class="form-control @if($errors->updateLeaveRequest->has('end_date')) is-invalid @endif" value="{{ old('end_date', $leaveRequest->end_date?->format('Y-m-d')) }}" required>
                        @if($errors->updateLeaveRequest->has('end_date'))
                            <div class="invalid-feedback">{{ $errors->updateLeaveRequest->first('end_date') }}</div>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label for="edit_reason" class="form-label fw-semibold">Alasan Izin</label>
                    <textarea id="edit_reason" name="reason" class="form-control @if($errors->updateLeaveRequest->has('reason')) is-invalid @endif" rows="3" required>{{ old('reason', $leaveRequest->reason) }}</textarea>
                    @if($errors->updateLeaveRequest->has('reason'))
                        <div class="invalid-feedback">{{ $errors->updateLeaveRequest->first('reason') }}</div>
                    @endif
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('pengurus.izin.index') }}" class="btn btn-link">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
