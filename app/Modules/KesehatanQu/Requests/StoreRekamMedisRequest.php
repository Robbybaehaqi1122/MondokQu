<?php

namespace App\Modules\KesehatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRekamMedisRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'golongan_darah' => ['nullable', 'string', 'max:5'],
            'riwayat_penyakit' => ['nullable', 'string'],
            'alergi_obat' => ['nullable', 'string'],
            'alergi_makanan' => ['nullable', 'string'],
            'tinggi_badan' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'berat_badan' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri harus dipilih.',
            'santri_id.exists' => 'Santri tidak ditemukan.',
            'golongan_darah.max' => 'Golongan darah maksimal 5 karakter.',
            'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
            'tinggi_badan.max' => 'Tinggi badan maksimal 300 cm.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.max' => 'Berat badan maksimal 500 kg.',
        ];
    }
}
