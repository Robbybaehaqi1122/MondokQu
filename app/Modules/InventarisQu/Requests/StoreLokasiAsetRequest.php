<?php

namespace App\Modules\InventarisQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLokasiAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'building' => 'nullable|string|max:200',
            'floor' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
        ];
    }
}
