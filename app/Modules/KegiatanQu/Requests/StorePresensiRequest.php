<?php

namespace App\Modules\KegiatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePresensiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'presensi' => ['required', 'array'],
            'presensi.*.santri_id' => ['required', 'exists:santris,id'],
            'presensi.*.status' => ['required', 'string', 'in:hadir,sakit,izin,alpha'],
            'presensi.*.catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'presensi.required' => 'Data presensi harus diisi.',
            'presensi.*.santri_id.required' => 'Santri harus dipilih.',
            'presensi.*.santri_id.exists' => 'Santri tidak ditemukan.',
            'presensi.*.status.required' => 'Status presensi harus diisi.',
            'presensi.*.status.in' => 'Status presensi tidak valid.',
        ];
    }
}
