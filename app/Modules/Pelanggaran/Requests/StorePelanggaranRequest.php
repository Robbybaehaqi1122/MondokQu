<?php

namespace App\Modules\Pelanggaran\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePelanggaranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'kategori_id' => ['required', 'exists:pelanggaran_kategoris,id'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'tanggal' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Pilih santri yang melanggar.',
            'kategori_id.required' => 'Pilih kategori pelanggaran.',
            'tanggal.required' => 'Tanggal pelanggaran harus diisi.',
        ];
    }
}
