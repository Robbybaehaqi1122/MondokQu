<?php

namespace App\Modules\Admin\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('assign roles') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;
        $tenantId = $isSuperAdmin ? $this->input('tenant_id') ?: null : $user?->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class, 'name')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'tenant_id' => [
                'nullable',
                'integer',
                Rule::exists('tenants', 'id'),
            ],
        ];
    }
}
