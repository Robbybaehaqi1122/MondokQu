<?php

namespace App\Modules\PpdbQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeleksiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'jenis' => ['required', 'string', 'in:administrasi,tes_baca_quran,wawancara'],
            'nilai' => ['nullable', 'integer', 'min:0', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'hasil' => ['required', 'string', 'in:lulus,tidak_lulus'],
            'tanggal_seleksi' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis seleksi harus diisi.',
            'jenis.in' => 'Jenis seleksi tidak valid.',
            'nilai.integer' => 'Nilai harus berupa angka.',
            'nilai.min' => 'Nilai minimal 0.',
            'nilai.max' => 'Nilai maksimal 100.',
            'hasil.required' => 'Hasil seleksi harus diisi.',
            'hasil.in' => 'Hasil seleksi tidak valid.',
        ];
    }
}
