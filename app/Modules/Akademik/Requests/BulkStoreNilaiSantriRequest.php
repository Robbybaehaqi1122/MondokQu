<?php

namespace App\Modules\Akademik\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreNilaiSantriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tenant_id;
    }

    public function rules(): array
    {
        return [
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'semester' => ['required', 'string', 'max:50'],
            'room_id' => ['required', 'exists:rooms,id'],
            'grades' => ['required', 'array'],
            'grades.*.santri_id' => ['required', 'exists:santris,id'],
            'grades.*.nilai_pengetahuan' => ['required', 'integer', 'min:0', 'max:100'],
            'grades.*.nilai_keterampilan' => ['required', 'integer', 'min:0', 'max:100'],
            'grades.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
            'semester.required' => 'Semester wajib dipilih.',
            'room_id.required' => 'Ruangan wajib dipilih.',
            'grades.required' => 'Minimal satu nilai harus diisi.',
            'grades.*.santri_id.required' => 'Santri wajib dipilih.',
            'grades.*.nilai_pengetahuan.required' => 'Nilai pengetahuan wajib diisi.',
            'grades.*.nilai_pengetahuan.min' => 'Nilai pengetahuan minimal 0.',
            'grades.*.nilai_pengetahuan.max' => 'Nilai pengetahuan maksimal 100.',
            'grades.*.nilai_keterampilan.required' => 'Nilai keterampilan wajib diisi.',
            'grades.*.nilai_keterampilan.min' => 'Nilai keterampilan minimal 0.',
            'grades.*.nilai_keterampilan.max' => 'Nilai keterampilan maksimal 100.',
        ];
    }
}
