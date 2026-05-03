<?php

namespace App\Http\Requests\Santri;

use App\Models\Santri;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSantriInvoiceRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'createInvoice';

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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'santri_id' => [
                'required',
                'integer',
                Rule::exists(Santri::class, 'id')
                    ->when(! ($user?->isSuperAdmin() ?? false), fn ($rule) => $rule->where('tenant_id', $user?->tenant_id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'period_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'period_year' => ['nullable', 'integer', 'digits:4', 'min:2000', 'max:'.(now()->year + 1)],
            'due_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'santri_id.required' => 'Santri wajib dipilih.',
            'santri_id.exists' => 'Santri tidak ditemukan atau berada di tenant lain.',
            'title.required' => 'Nama tagihan wajib diisi.',
            'period_month.min' => 'Bulan periode tidak valid.',
            'period_month.max' => 'Bulan periode tidak valid.',
            'period_year.digits' => 'Tahun periode harus 4 digit.',
            'due_date.required' => 'Jatuh tempo wajib diisi.',
            'due_date.date' => 'Jatuh tempo harus berupa tanggal yang valid.',
            'amount.required' => 'Nominal tagihan wajib diisi.',
            'amount.numeric' => 'Nominal tagihan harus berupa angka.',
            'amount.min' => 'Nominal tagihan minimal Rp 1.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
