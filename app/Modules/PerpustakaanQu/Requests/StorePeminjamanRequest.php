<?php

namespace App\Modules\PerpustakaanQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePeminjamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kitab_id' => ['required', 'exists:perpustakaan_kitabs,id'],
            'santri_id' => ['required', 'exists:santris,id'],
            'tanggal_pinjam' => ['required', 'date'],
            'tanggal_jatuh_tempo' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
