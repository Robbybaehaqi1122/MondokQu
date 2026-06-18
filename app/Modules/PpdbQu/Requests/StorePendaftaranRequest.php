<?php

namespace App\Modules\PpdbQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendaftaranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gelombang_id' => ['required', 'exists:ppdb_gelombangs,id'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'string', 'in:laki-laki,perempuan'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'asal_sekolah' => ['nullable', 'string', 'max:255'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'no_hp_orangtua' => ['nullable', 'string', 'max:20'],
            'berkas' => ['nullable', 'array'],
            'berkas.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'gelombang_id.required' => 'Gelombang pendaftaran harus dipilih.',
            'gelombang_id.exists' => 'Gelombang tidak ditemukan.',
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin harus diisi.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'no_hp.required' => 'No. HP harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'berkas.*.mimes' => 'Berkas harus berupa PDF, JPG, atau PNG.',
            'berkas.*.max' => 'Berkas maksimal 2 MB.',
        ];
    }
}
