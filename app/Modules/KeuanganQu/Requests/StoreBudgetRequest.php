<?php

namespace App\Modules\KeuanganQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coa_account_id' => 'required|exists:coa_accounts,id',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020|max:2099',
            'amount' => 'required|integer|min:0',
            'description' => 'nullable|string|max:500',
        ];
    }
}
