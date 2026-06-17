<?php

namespace App\Modules\KegiatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePertemuanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kegiatan_id' => ['required', 'exists:kegiatans,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'materi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'kegiatan_id.required' => 'Kegiatan harus dipilih.',
            'kegiatan_id.exists' => 'Kegiatan tidak ditemukan.',
            'tanggal.required' => 'Tanggal pertemuan harus diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid (HH:MM).',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
