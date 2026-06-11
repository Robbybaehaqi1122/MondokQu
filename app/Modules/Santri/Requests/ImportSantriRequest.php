<?php

namespace App\Modules\Santri\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ImportSantriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types(['csv', 'xlsx', 'xls'])
                    ->max(10240),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File CSV atau Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat CSV, XLSX, atau XLS.',
        ];
    }
}
