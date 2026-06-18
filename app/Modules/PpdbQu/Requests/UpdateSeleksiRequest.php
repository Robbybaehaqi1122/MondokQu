<?php

namespace App\Modules\PpdbQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeleksiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nilai' => ['nullable', 'integer', 'min:0', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'hasil' => ['required', 'string', 'in:lulus,tidak_lulus'],
            'tanggal_seleksi' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'hasil.required' => 'Hasil seleksi harus diisi.',
            'hasil.in' => 'Hasil seleksi tidak valid.',
        ];
    }
}
