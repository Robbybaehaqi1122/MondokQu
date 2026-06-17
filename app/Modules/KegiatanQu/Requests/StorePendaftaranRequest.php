<?php

namespace App\Modules\KegiatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendaftaranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kegiatan_id' => ['required', 'exists:kegiatans,id'],
            'santri_id' => ['required', 'exists:santris,id'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'kegiatan_id.required' => 'Kegiatan harus dipilih.',
            'kegiatan_id.exists' => 'Kegiatan tidak ditemukan.',
            'santri_id.required' => 'Santri harus dipilih.',
            'santri_id.exists' => 'Santri tidak ditemukan.',
        ];
    }
}
