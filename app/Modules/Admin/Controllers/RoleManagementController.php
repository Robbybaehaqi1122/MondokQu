<?php

namespace App\Modules\Admin\Controllers;

use App\Modules\Admin\Requests\StoreRoleRequest;
use App\Modules\Admin\Requests\UpdateRolePermissionsRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleManagementController extends \App\Http\Controllers\Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(): View
    {
        $currentUser = request()->user();
        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => (string) str($permission->name)->before(' ')->headline());

        return view('admin.roles', [
            'roles' => Role::query()
                ->forTenant($currentUser?->isSuperAdmin() ? null : $currentUser?->tenant_id)
                ->with('permissions')
                ->withCount('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get(),
            'permissionGroups' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        if (! $request->user()->isSuperAdmin() && in_array($request->validated('name'), ['Superadmin', 'Admin'])) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Hanya Superadmin yang dapat membuat role Admin atau Superadmin.');
        }

        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $this->activityLogger->log(
            action: 'role_created',
            actor: $request->user(),
            target: $role,
            description: 'Role baru dibuat.',
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role baru berhasil dibuat.');
    }

    public function updatePermissions(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        $currentUser = $request->user();

        if ($role->name === 'Superadmin' && ! $currentUser->isSuperAdmin()) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Hanya Superadmin yang dapat mengubah permission role Superadmin.');
        }

        if (! $currentUser->isSuperAdmin() && $role->tenant_id !== $currentUser->tenant_id) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Anda tidak dapat mengubah role dari tenant pondok lain.');
        }

        $previousPermissions = $role->permissions()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $permissionIds = collect($request->validated('permissions', []))
            ->map(fn (mixed $permissionId): int => (int) $permissionId);

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get();

        $role->syncPermissions($permissions);

        $this->activityLogger->log(
            action: 'role_permissions_updated',
            actor: $currentUser,
            target: $role,
            description: 'Permission untuk role diperbarui.',
            properties: [
                'permissions' => [
                    'before' => $previousPermissions,
                    'after' => $permissions->pluck('name')->values()->all(),
                ],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Permission untuk role berhasil diperbarui.');
    }
}
