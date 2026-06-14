<?php

namespace App\Modules\Tahfidz\Requests;

use App\Models\MemorizationSchedule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMemorizationScheduleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'musyrif_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'string', 'in:'.implode(',', MemorizationSchedule::daysOfWeek())],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'max_santri' => ['required', 'integer', 'min:1', 'max:255'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'musyrif_id.required' => 'Pilih musyrif/ustadz.',
            'day_of_week.required' => 'Pilih hari.',
            'day_of_week.in' => 'Hari yang dipilih tidak valid.',
            'start_time.required' => 'Jam mulai harus diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid (gunakan H:i).',
            'end_time.required' => 'Jam selesai harus diisi.',
            'end_time.date_format' => 'Format jam selesai tidak valid (gunakan H:i).',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
            'max_santri.required' => 'Jumlah maksimal santri harus diisi.',
            'max_santri.integer' => 'Jumlah maksimal santri harus berupa angka.',
            'max_santri.min' => 'Minimal 1 santri.',
            'room_id.exists' => 'Ruangan yang dipilih tidak valid.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
