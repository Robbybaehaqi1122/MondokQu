<?php

namespace App\Modules\KesehatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreObatRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nama_obat' => ['required', 'string', 'max:255'],
            'jenis' => ['nullable', 'string', 'max:50'],
            'stok' => ['nullable', 'integer', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:20'],
            'expired_date' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_obat.required' => 'Nama obat harus diisi.',
            'nama_obat.max' => 'Nama obat maksimal 255 karakter.',
            'stok.integer' => 'Stok harus berupa angka.',
            'stok.min' => 'Stok tidak boleh negatif.',
            'satuan.max' => 'Satuan maksimal 20 karakter.',
            'expired_date.date' => 'Format tanggal expired tidak valid.',
        ];
    }
}
