<?php

namespace App\Modules\InventarisQu\Requests;

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
            'aset_id' => 'required|exists:asets,id',
            'peminjam' => 'required|string|max:200',
            'role_peminjam' => 'nullable|string|max:100',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'nullable|date|after_or_equal:tanggal_pinjam',
            'tujuan' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:500',
        ];
    }
}
