<?php

namespace App\Http\Requests\Santri;

use App\Models\Santri;
use App\Models\SantriInvoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSantriInvoiceRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'updateInvoice';

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
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $invoice = $this->route('invoice');

            if (! $invoice instanceof SantriInvoice) {
                return;
            }

            $user = $this->user();
            if (! ($user?->isSuperAdmin() ?? false) && $invoice->tenant_id !== $user?->tenant_id) {
                $validator->errors()->add('santri_id', 'Tagihan tidak ditemukan atau berada di tenant lain.');

                return;
            }

            $paidAmount = (float) $invoice->payments()->sum('amount');
            $targetSantri = Santri::query()->find($this->input('santri_id'));

            if ($targetSantri && (int) $targetSantri->tenant_id !== (int) $invoice->tenant_id) {
                $validator->errors()->add('santri_id', 'Santri pengganti harus berada di tenant yang sama dengan tagihan.');
            }

            if ((float) $this->input('amount', 0) < $paidAmount) {
                $validator->errors()->add('amount', 'Nominal tagihan tidak boleh lebih kecil dari total pembayaran yang sudah dicatat.');
            }

            if ((int) $this->input('santri_id') !== (int) $invoice->santri_id && $paidAmount > 0) {
                $validator->errors()->add('santri_id', 'Santri pada tagihan yang sudah memiliki pembayaran tidak dapat diganti.');
            }
        });
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
