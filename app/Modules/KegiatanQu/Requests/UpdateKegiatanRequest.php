<?php

namespace App\Modules\KegiatanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKegiatanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'pembina_id' => ['nullable', 'exists:users,id'],
            'jadwal' => ['nullable', 'array'],
            'jadwal.*.hari' => ['required_with:jadwal', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jadwal.*.jam_mulai' => ['required_with:jadwal', 'date_format:H:i'],
            'jadwal.*.jam_selesai' => ['nullable', 'date_format:H:i', 'after:jadwal.*.jam_mulai'],
            'tempat' => ['nullable', 'string', 'max:255'],
            'kuota' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'in:aktif,nonaktif'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kegiatan harus diisi.',
            'jadwal.*.hari.required_with' => 'Hari jadwal harus diisi.',
            'jadwal.*.hari.in' => 'Hari jadwal tidak valid.',
            'jadwal.*.jam_mulai.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'jadwal.*.jam_selesai.date_format' => 'Format jam selesai tidak valid (HH:MM).',
            'jadwal.*.jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'kuota.integer' => 'Kuota harus berupa angka.',
            'status.in' => 'Status harus aktif atau nonaktif.',
        ];
    }
}
