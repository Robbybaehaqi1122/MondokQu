<?php

namespace App\Modules\KepengurusanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengurusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage kepengurusan') ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:5000'],
            'foto' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pengurus wajib diisi.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat PNG, JPG, atau JPEG.',
            'foto.max' => 'Foto maksimal 2 MB.',
        ];
    }
}
