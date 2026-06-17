<?php

namespace App\Modules\InventarisQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => 'required|exists:kategori_asets,id',
            'lokasi_id' => 'required|exists:lokasi_asets,id',
            'name' => 'required|string|max:200',
            'merk' => 'nullable|string|max:200',
            'tahun_perolehan' => 'nullable|integer|min:1900|max:2099',
            'harga_perolehan' => 'nullable|integer|min:0',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'deskripsi' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }
}
