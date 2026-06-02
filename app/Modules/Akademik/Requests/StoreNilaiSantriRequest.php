<?php

namespace App\Modules\Akademik\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNilaiSantriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tenant_id;
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'semester' => ['required', 'string', 'max:50'],
            'nilai_pengetahuan' => ['required', 'integer', 'min:0', 'max:100'],
            'nilai_keterampilan' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri wajib dipilih.',
            'mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
            'semester.required' => 'Semester wajib dipilih.',
            'nilai_pengetahuan.required' => 'Nilai pengetahuan wajib diisi.',
            'nilai_pengetahuan.min' => 'Nilai pengetahuan minimal 0.',
            'nilai_pengetahuan.max' => 'Nilai pengetahuan maksimal 100.',
            'nilai_keterampilan.required' => 'Nilai keterampilan wajib diisi.',
            'nilai_keterampilan.min' => 'Nilai keterampilan minimal 0.',
            'nilai_keterampilan.max' => 'Nilai keterampilan maksimal 100.',
        ];
    }
}
