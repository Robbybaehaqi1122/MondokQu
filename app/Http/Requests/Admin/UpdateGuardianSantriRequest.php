<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuardianSantriRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('santri_ids')) {
            $this->merge([
                'santri_ids' => [],
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'relationship' => ['nullable', 'string', 'max:50'],
            'santri_ids' => ['array'],
            'santri_ids.*' => ['integer', 'distinct'],
        ];
    }
}
