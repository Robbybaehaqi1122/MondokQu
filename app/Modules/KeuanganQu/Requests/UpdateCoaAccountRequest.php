<?php

namespace App\Modules\KeuanganQu\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoaAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:coa_accounts,id',
            'code' => ['required', 'string', 'max:20', Rule::unique('coa_accounts', 'code')->ignore($this->coaAccount)],
            'name' => 'required|string|max:255',
            'type' => 'required|in:aset,kewajiban,modal,pendapatan,beban',
            'normal_balance' => 'required|in:debit,kredit',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];
    }
}
