<?php

namespace App\Modules\Saas\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTenantRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'deleteTenant';

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
            'delete_tenant_id' => ['required', 'integer'],
            'tenant_delete_confirmation' => ['required', 'string', 'max:255'],
            'delete_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Tenant|null $tenant */
            $tenant = $this->route('tenant');

            if (! $tenant) {
                return;
            }

            if ((int) $this->input('delete_tenant_id') !== $tenant->id) {
                $validator->errors()->add('delete_tenant_id', 'Tenant yang akan dihapus tidak valid.');
            }

            if ($tenant->isDeleting()) {
                $validator->errors()->add('delete_tenant_id', 'Tenant ini sudah masuk antrean penghapusan.');
            }

            if ((string) $this->input('tenant_delete_confirmation') !== $tenant->slug) {
                $validator->errors()->add(
                    'tenant_delete_confirmation',
                    'Ketik slug tenant persis untuk menghapus tenant permanen.'
                );
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
            'delete_tenant_id.required' => 'Tenant yang akan dihapus wajib dipilih.',
            'tenant_delete_confirmation.required' => 'Ketik slug tenant terlebih dahulu.',
            'tenant_delete_confirmation.max' => 'Konfirmasi slug tenant maksimal 255 karakter.',
            'delete_reason.max' => 'Catatan penghapusan maksimal 1000 karakter.',
        ];
    }
}
