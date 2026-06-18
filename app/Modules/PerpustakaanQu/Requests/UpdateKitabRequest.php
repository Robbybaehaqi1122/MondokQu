<?php

namespace App\Modules\PerpustakaanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKitabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'exists:perpustakaan_kategoris,id'],
            'judul' => ['required', 'string', 'max:255'],
            'pengarang' => ['nullable', 'string', 'max:255'],
            'penerbit' => ['nullable', 'string', 'max:255'],
            'tahun_terbit' => ['nullable', 'integer', 'min:1000', 'max:' . (now()->year + 1)],
            'isbn' => ['nullable', 'string', 'max:50'],
            'lokasi_rak' => ['nullable', 'string', 'max:100'],
            'jumlah_eksemplar' => ['required', 'integer', 'min:1'],
            'kondisi' => ['required', 'in:baik,rusak_ringan,rusak_berat, hilang'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
