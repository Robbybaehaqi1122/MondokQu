<?php

namespace App\Modules\Admin\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRolesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage system settings') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists(Role::class, 'id')
                ->where(fn ($query) => $query->where('tenant_id', $this->user()?->isSuperAdmin() ? null : $this->user()?->tenant_id)),
            ],
        ];
    }
}
