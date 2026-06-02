<?php

namespace App\Modules\Akademik\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNilaiSantriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->tenant_id;
    }

    public function rules(): array
    {
        return [
            'nilai_pengetahuan' => ['required', 'integer', 'min:0', 'max:100'],
            'nilai_keterampilan' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nilai_pengetahuan.required' => 'Nilai pengetahuan wajib diisi.',
            'nilai_keterampilan.required' => 'Nilai keterampilan wajib diisi.',
        ];
    }
}
