<?php

namespace App\Modules\KeuanganQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|integer|min:0',
            'description' => 'nullable|string|max:500',
        ];
    }
}
