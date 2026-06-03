<?php

namespace App\Actions\Saas;

use App\Models\Role;
use App\Models\Tenant;

class CreateTenantRoles
{
    private const TENANT_ROLES = [
        'Admin',
        'Pengurus',
        'Bendahara',
        'Musyrif/Ustadz',
        'Wali Santri',
    ];

    public function handle(Tenant $tenant): void
    {
        foreach (self::TENANT_ROLES as $roleName) {
            $template = Role::whereNull('tenant_id')->where('name', $roleName)->first();

            $tenantRole = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'tenant_id' => $tenant->id,
            ]);

            if ($template) {
                $tenantRole->syncPermissions($template->permissions);
            }
        }
    }
}
