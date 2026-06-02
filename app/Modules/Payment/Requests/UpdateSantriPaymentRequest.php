<?php

namespace App\Modules\Payment\Requests;

use App\Models\SantriPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSantriPaymentRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'updatePayment';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit historical pembayaran') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:100', 'max:99999999999'],
            'paid_at' => ['required', 'date', 'before_or_equal:now'],
            'payment_method' => ['required', 'string', Rule::in(SantriPayment::paymentMethods())],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $this->merge(['amount' => (int) ((float) $this->input('amount') * 100)]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $payment = $this->route('payment');

            if (! $payment instanceof SantriPayment) {
                return;
            }

            $user = $this->user();
            if (! ($user?->isSuperAdmin() ?? false) && $payment->tenant_id !== $user?->tenant_id) {
                $validator->errors()->add('amount', 'Pembayaran tidak ditemukan atau berada di tenant lain.');

                return;
            }

            $invoice = $payment->invoice;
            if (! $invoice) {
                $validator->errors()->add('amount', 'Tagihan pembayaran tidak ditemukan.');

                return;
            }

            $otherPaymentTotal = $invoice->payments()
                ->whereKeyNot($payment->id)
                ->sum('amount');
            $maxAllowedAmount = max(0, $invoice->amount - $otherPaymentTotal);

            if ((int) $this->input('amount', 0) > $maxAllowedAmount) {
                $validator->errors()->add('amount', 'Nominal koreksi melebihi sisa tagihan setelah pembayaran lain.');
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
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka.',
            'amount.min' => 'Nominal pembayaran minimal Rp 1.',
            'paid_at.required' => 'Tanggal bayar wajib diisi.',
            'paid_at.date' => 'Tanggal bayar harus berupa tanggal yang valid.',
            'paid_at.before_or_equal' => 'Tanggal bayar tidak boleh melebihi waktu sekarang.',
            'payment_method.required' => 'Metode bayar wajib dipilih.',
            'payment_method.in' => 'Metode bayar yang dipilih tidak valid.',
            'reference_number.max' => 'No. referensi maksimal 100 karakter.',
            'note.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
