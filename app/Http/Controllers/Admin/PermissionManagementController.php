<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRolesRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Display the permission management panel.
     */
    public function index(): View
    {
        return view('admin.permissions', [
            'permissions' => Permission::query()
                ->with('roles')
                ->withCount('roles')
                ->orderBy('name')
                ->get(),
            'roles' => Role::query()
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $permission = Permission::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $this->activityLogger->log(
            action: 'permission_created',
            actor: $request->user(),
            target: $permission,
            description: 'Permission baru dibuat.',
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.permissions')
            ->with('success', 'Permission baru berhasil dibuat.');
    }

    /**
     * Update a permission name.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $previousName = $permission->name;

        $permission->update([
            'name' => $request->validated('name'),
        ]);

        $this->activityLogger->log(
            action: 'permission_updated',
            actor: $request->user(),
            target: $permission,
            description: 'Nama permission diperbarui.',
            properties: [
                'from' => $previousName,
                'to' => $request->validated('name'),
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.permissions')
            ->with('success', 'Nama permission berhasil diperbarui.');
    }

    /**
     * Sync roles assigned to a permission.
     */
    public function updateRoles(UpdatePermissionRolesRequest $request, Permission $permission): RedirectResponse
    {
        $previousRoles = $permission->roles()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $roleIds = collect($request->validated('roles', []))
            ->map(fn (mixed $roleId): int => (int) $roleId);

        $roles = Role::query()
            ->whereIn('id', $roleIds)
            ->get();

        $permission->syncRoles($roles);

        $this->activityLogger->log(
            action: 'permission_roles_updated',
            actor: $request->user(),
            target: $permission,
            description: 'Mapping role untuk permission diperbarui.',
            properties: [
                'roles' => [
                    'before' => $previousRoles,
                    'after' => $roles->pluck('name')->values()->all(),
                ],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()
            ->route('admin.permissions')
            ->with('success', 'Mapping role untuk permission berhasil diperbarui.');
    }
}
