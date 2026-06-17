<?php

namespace App\Modules\KegiatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNilaiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kegiatan_id' => ['required', 'exists:kegiatans,id'],
            'santri_id' => ['required', 'exists:santris,id'],
            'aspek' => ['required', 'string', 'max:100'],
            'nilai' => ['nullable', 'integer', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'kegiatan_id.required' => 'Kegiatan harus dipilih.',
            'santri_id.required' => 'Santri harus dipilih.',
            'aspek.required' => 'Aspek penilaian harus diisi.',
            'nilai.integer' => 'Nilai harus berupa angka.',
            'nilai.min' => 'Nilai minimal 0.',
            'nilai.max' => 'Nilai maksimal 100.',
        ];
    }
}
