<?php

namespace App\Modules\KesehatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImunisasiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'jenis_imunisasi' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'status' => ['required', 'in:sudah,belum'],
            'catatan' => ['nullable', 'string'],
            'diberikan_oleh' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri harus dipilih.',
            'santri_id.exists' => 'Santri yang dipilih tidak valid.',
            'jenis_imunisasi.required' => 'Jenis imunisasi harus diisi.',
            'jenis_imunisasi.max' => 'Jenis imunisasi maksimal 255 karakter.',
            'tanggal.required' => 'Tanggal imunisasi harus diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'status.required' => 'Status imunisasi harus dipilih.',
            'status.in' => 'Status imunisasi harus "sudah" atau "belum".',
            'diberikan_oleh.exists' => 'Petugas yang dipilih tidak valid.',
        ];
    }
}
