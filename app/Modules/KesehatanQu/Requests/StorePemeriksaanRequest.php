<?php

namespace App\Modules\KesehatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePemeriksaanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'tanggal_pemeriksaan' => ['required', 'date'],
            'keluhan' => ['required', 'string', 'max:255'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'tindakan' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'rujuk' => ['nullable', 'boolean'],
            'tempat_rujukan' => ['required_if:rujuk,true', 'nullable', 'string', 'max:255'],
            'diagnosis_dokter' => ['nullable', 'string', 'max:255'],
            'tanggal_rujuk' => ['required_if:rujuk,true', 'nullable', 'date'],
            'tanggal_kembali' => ['nullable', 'date', 'after_or_equal:tanggal_rujuk'],
            'catatan_rujukan' => ['nullable', 'string'],
            'obat_ids' => ['nullable', 'array'],
            'obat_ids.*' => ['nullable', 'exists:kesehatan_obats,id'],
            'obat_jumlahs' => ['nullable', 'array'],
            'obat_jumlahs.*' => ['nullable', 'integer', 'min:1'],
            'obat_catatans' => ['nullable', 'array'],
            'obat_catatans.*' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri harus dipilih.',
            'santri_id.exists' => 'Santri tidak ditemukan.',
            'tanggal_pemeriksaan.required' => 'Tanggal pemeriksaan harus diisi.',
            'tanggal_pemeriksaan.date' => 'Format tanggal tidak valid.',
            'keluhan.required' => 'Keluhan harus diisi.',
            'keluhan.max' => 'Keluhan maksimal 255 karakter.',
            'tempat_rujukan.required_if' => 'Tempat rujukan harus diisi jika ada rujukan.',
            'tanggal_rujuk.required_if' => 'Tanggal rujuk harus diisi jika ada rujukan.',
            'obat_ids.*.exists' => 'Obat tidak ditemukan.',
        ];
    }
}
