<?php

namespace App\Modules\InventarisQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKategoriAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ];
    }
}
