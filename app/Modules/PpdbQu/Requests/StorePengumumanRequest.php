<?php

namespace App\Modules\PpdbQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengumumanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gelombang_id' => ['required', 'exists:ppdb_gelombangs,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_pengumuman' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'gelombang_id.required' => 'Gelombang harus dipilih.',
            'gelombang_id.exists' => 'Gelombang tidak ditemukan.',
            'judul.required' => 'Judul pengumuman harus diisi.',
            'tanggal_pengumuman.required' => 'Tanggal pengumuman harus diisi.',
        ];
    }
}
