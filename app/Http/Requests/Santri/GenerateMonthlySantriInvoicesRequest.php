<?php

namespace App\Http\Requests\Santri;

use Illuminate\Foundation\Http\FormRequest;

class GenerateMonthlySantriInvoicesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create pembayaran') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $periodYearMin = (int) config('santri.invoice.period_year_min', 2000);
        $periodYearMax = now()->year + max(1, (int) config('santri.invoice.period_year_future_limit', 5));

        return [
            'title' => ['required', 'string', 'max:255'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'digits:4', 'min:'.$periodYearMin, 'max:'.$periodYearMax],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'mode' => ['required', 'in:preview,dispatch'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mode' => $this->input('mode', 'dispatch'),
        ]);
    }
}
