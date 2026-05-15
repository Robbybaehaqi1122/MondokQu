<?php

namespace App\Modules\Saas\Requests;

use App\Models\Tenant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreBillingNoteRequest extends FormRequest
{
    /**
     * The name of the error bag.
     *
     * @var string
     */
    protected $errorBag = 'billingNote';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Superadmin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:50', Rule::in(['transfer bank', 'cash', 'e-wallet', 'qris', 'lainnya'])],
            'period_starts_at' => ['required', 'date'],
            'period_ends_at' => ['required', 'date', 'after_or_equal:period_starts_at'],
            'apply_subscription' => ['nullable', 'boolean'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $tenantId = $this->input('tenant_id');

            if (! is_scalar($tenantId)) {
                return;
            }

            $tenant = Tenant::query()->find($tenantId);

            if ($tenant?->isDeleting()) {
                $validator->errors()->add('tenant_id', 'Tenant sedang dihapus sehingga tidak dapat menerima billing note baru.');

                return;
            }

            if (! $this->boolean('apply_subscription') || ! $this->filled('period_ends_at')) {
                return;
            }

            if (Carbon::parse((string) $this->input('period_ends_at'))->endOfDay()->isPast()) {
                $validator->errors()->add(
                    'period_ends_at',
                    'Tanggal akhir periode harus masih berlaku jika ingin sekaligus mengaktifkan subscription.'
                );
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
            'tenant_id.required' => 'Tenant wajib dipilih.',
            'tenant_id.exists' => 'Tenant yang dipilih tidak valid.',
            'paid_at.required' => 'Tanggal bayar wajib diisi.',
            'paid_at.date' => 'Tanggal bayar harus berupa tanggal yang valid.',
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka.',
            'amount.min' => 'Nominal pembayaran tidak boleh negatif.',
            'payment_method.required' => 'Metode bayar wajib dipilih.',
            'payment_method.max' => 'Metode bayar maksimal 50 karakter.',
            'payment_method.in' => 'Metode bayar yang dipilih tidak valid.',
            'period_starts_at.required' => 'Tanggal mulai periode wajib diisi.',
            'period_starts_at.date' => 'Tanggal mulai periode harus valid.',
            'period_ends_at.required' => 'Tanggal akhir periode wajib diisi.',
            'period_ends_at.date' => 'Tanggal akhir periode harus valid.',
            'period_ends_at.after_or_equal' => 'Tanggal akhir periode harus sama atau setelah tanggal mulai periode.',
            'apply_subscription.boolean' => 'Pilihan aktivasi subscription tidak valid.',
            'admin_note.max' => 'Catatan billing maksimal 1000 karakter.',
        ];
    }
}
