<?php

namespace App\Modules\KitabQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetoranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage kitab') ?? false;
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'kitab_id' => ['required', 'exists:kitab_kitabs,id'],
            'tanggal_setoran' => ['required', 'date'],
            'materi' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
