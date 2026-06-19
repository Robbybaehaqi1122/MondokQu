<?php

namespace App\Modules\KitabQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKitabRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage kitab') ?? false;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['nullable', 'exists:kitab_kategoris,id'],
            'nama' => ['required', 'string', 'max:255'],
            'pengarang' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
