<?php

namespace App\Modules\Pelanggaran\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePelanggaranKategoriRequest extends FormRequest
{
    public function rules(): array
    {
        $kategoriId = $this->route('pelanggaran_kategori')?->id ?? $this->route('pelanggaranKategori')?->id;

        return [
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('pelanggaran_kategoris', 'nama')
                    ->where('tenant_id', $this->user()?->tenant_id)
                    ->ignore($kategoriId),
            ],
            'poin' => ['required', 'integer', 'min:1', 'max:999'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori harus diisi.',
            'nama.unique' => 'Kategori dengan nama ini sudah ada.',
            'poin.required' => 'Poin pelanggaran harus diisi.',
            'poin.min' => 'Poin minimal 1.',
            'poin.max' => 'Poin maksimal 999.',
        ];
    }
}
