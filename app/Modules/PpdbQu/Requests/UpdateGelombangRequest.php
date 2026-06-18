<?php

namespace App\Modules\PpdbQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGelombangRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'kuota' => ['nullable', 'integer', 'min:0'],
            'biaya_pendaftaran' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:aktif,selesai'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama gelombang harus diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ];
    }
}
