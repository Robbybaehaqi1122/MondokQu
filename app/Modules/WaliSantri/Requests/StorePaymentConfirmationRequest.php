<?php

namespace App\Modules\WaliSantri\Requests;

use App\Models\SantriPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StorePaymentConfirmationRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'paymentConfirmation';

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
        return [
            'confirmation_invoice_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:100'],
            'paid_at' => ['required', 'date', 'before_or_equal:now'],
            'payment_method' => ['required', 'string', Rule::in(SantriPayment::paymentMethods())],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
            'proof' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(4096),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('amount')) {
            $this->merge(['amount' => (int) ((float) $this->input('amount') * 100)]);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation_invoice_id.required' => 'Tagihan tidak valid.',
            'amount.required' => 'Nominal transfer wajib diisi.',
            'amount.min' => 'Nominal transfer minimal Rp 1.',
            'paid_at.required' => 'Tanggal transfer wajib diisi.',
            'paid_at.before_or_equal' => 'Tanggal transfer tidak boleh melebihi waktu sekarang.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'proof.required' => 'Bukti bayar wajib diunggah.',
            'proof.image' => 'Bukti bayar harus berupa gambar.',
            'proof.max' => 'Ukuran bukti bayar maksimal 4 MB.',
            'note.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
