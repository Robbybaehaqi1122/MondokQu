<?php

namespace App\Modules\KepengurusanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage kepengurusan') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'pendidikan' => ['nullable', 'string', 'max:255'],
            'bidang_keahlian' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:5000'],
            'foto' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pengajar wajib diisi.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat PNG, JPG, atau JPEG.',
            'foto.max' => 'Foto maksimal 2 MB.',
        ];
    }
}
