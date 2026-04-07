<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRolePermissionsRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Display the role management panel.
     */
    public function index(): View
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => (string) str($permission->name)->before(' ')->headline());

        return view('admin.roles', [
            'roles' => Role::query()
                ->with('permissions')
                ->withCount('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get(),
            'permissionGroups' => $permissions,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
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

    /**
     * Sync permissions for the selected role.
     */
    public function updatePermissions(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        $permissionIds = collect($request->validated('permissions', []))
            ->map(fn (mixed $permissionId): int => (int) $permissionId);

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get();

        $role->syncPermissions($permissions);

        $this->activityLogger->log(
            action: 'role_permissions_updated',
            actor: $request->user(),
            target: $role,
            description: 'Permission untuk role diperbarui.',
            properties: [
                'permissions' => $permissions->pluck('name')->values()->all(),
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Permission untuk role berhasil diperbarui.');
    }
}
