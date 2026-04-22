<?php

namespace App\Modules\Saas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantSubscriptionRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'subscriptionControl';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:activate_trial,extend_trial,activate_subscription,mark_grace,mark_expired'],
            'trial_ends_at' => ['nullable', 'date', 'after:now'],
            'grace_ends_at' => ['nullable', 'date', 'after:now'],
            'subscription_duration' => ['nullable', 'string', 'in:1_month,3_months,6_months,12_months'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $action = $this->string('action')->toString();

            if (in_array($action, ['activate_trial', 'extend_trial'], true) && ! $this->filled('trial_ends_at')) {
                $validator->errors()->add('trial_ends_at', 'Silakan pilih tanggal akhir trial terlebih dahulu.');
            }

            if ($action === 'mark_grace' && ! $this->filled('grace_ends_at')) {
                $validator->errors()->add('grace_ends_at', 'Silakan pilih tanggal akhir grace period terlebih dahulu.');
            }

            if ($action === 'activate_subscription' && ! $this->filled('subscription_duration')) {
                $validator->errors()->add('subscription_duration', 'Silakan pilih durasi subscription terlebih dahulu.');
            }
        });
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Aksi subscription wajib dipilih.',
            'action.in' => 'Aksi subscription yang dipilih tidak valid.',
            'trial_ends_at.date' => 'Tanggal akhir trial harus berupa tanggal yang valid.',
            'trial_ends_at.after' => 'Tanggal akhir trial harus lebih besar dari waktu sekarang.',
            'grace_ends_at.date' => 'Tanggal akhir grace period harus berupa tanggal yang valid.',
            'grace_ends_at.after' => 'Tanggal akhir grace period harus lebih besar dari waktu sekarang.',
            'subscription_duration.in' => 'Durasi subscription yang dipilih tidak valid.',
            'admin_note.max' => 'Catatan admin maksimal 1000 karakter.',
        ];
    }
}
