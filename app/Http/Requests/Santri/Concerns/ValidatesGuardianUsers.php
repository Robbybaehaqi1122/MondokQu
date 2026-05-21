<?php

namespace App\Http\Requests\Santri\Concerns;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait ValidatesGuardianUsers
{
    /**
     * Remove empty checkbox values before validating guardian IDs.
     */
    protected function normalizeGuardianUserIdsForValidation(): void
    {
        $guardianUserIds = $this->input('guardian_user_ids');

        if (! is_array($guardianUserIds)) {
            return;
        }

        $this->merge([
            'guardian_user_ids' => collect($guardianUserIds)
                ->filter(fn ($userId) => $userId !== null && $userId !== '')
                ->values()
                ->all(),
        ]);
    }

    /**
     * Ensure selected wali users belong to the santri tenant and have the Wali Santri role.
     */
    protected function guardianUsersExistRule(): Exists
    {
        return Rule::exists(User::class, 'id')
            ->where(function ($query): void {
                $query
                    ->where('tenant_id', $this->guardianTenantId())
                    ->whereExists(function ($roleQuery): void {
                        $roleQuery
                            ->selectRaw('1')
                            ->from('model_has_roles')
                            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                            ->whereColumn('model_has_roles.model_id', 'users.id')
                            ->where('model_has_roles.model_type', User::class)
                            ->where('roles.name', 'Wali Santri')
                            ->where('roles.guard_name', 'web');
                    });
            });
    }

    /**
     * Resolve the tenant that is allowed to own guardian users for this request.
     */
    protected function guardianTenantId(): ?int
    {
        $santri = $this->route('santri');

        if ($santri instanceof Santri) {
            return (int) $santri->tenant_id;
        }

        $tenantId = $this->user()?->tenant_id;

        return $tenantId ? (int) $tenantId : null;
    }

    /**
     * Return validated guardian user IDs as a clean integer collection.
     */
    public function guardianUserIds(): Collection
    {
        $validated = $this->validated();

        return collect($validated['guardian_user_ids'] ?? [])
            ->map(fn ($userId) => (int) $userId)
            ->unique()
            ->values();
    }
}
