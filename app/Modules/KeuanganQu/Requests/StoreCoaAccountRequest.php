<?php

namespace App\Modules\KeuanganQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoaAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:coa_accounts,id',
            'code' => 'required|string|max:20|unique:coa_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:aset,kewajiban,modal,pendapatan,beban',
            'normal_balance' => 'required|in:debit,kredit',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }
}
