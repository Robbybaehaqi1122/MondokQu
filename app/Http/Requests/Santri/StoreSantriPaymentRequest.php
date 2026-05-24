<?php

namespace App\Http\Requests\Santri;

use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSantriPaymentRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'recordPayment';

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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999.99'],
            'paid_at' => ['required', 'date', 'before_or_equal:now'],
            'payment_method' => ['required', 'string', Rule::in(SantriPayment::paymentMethods())],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
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
                $validator->errors()->add('amount', 'Tagihan tidak ditemukan atau berada di tenant lain.');

                return;
            }

            if ($invoice->status === SantriInvoice::STATUS_PAID) {
                $validator->errors()->add('amount', 'Tagihan ini sudah lunas.');

                return;
            }

            $amount = (float) $this->input('amount', 0);
            if ($amount > $invoice->outstandingAmount()) {
                $validator->errors()->add('amount', 'Nominal pembayaran melebihi sisa tagihan.');
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
