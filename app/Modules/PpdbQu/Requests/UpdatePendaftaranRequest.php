<?php

namespace App\Modules\PpdbQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePendaftaranRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:menunggu,diproses,diterima,ditolak,daftar_ulang'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status harus diisi.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
