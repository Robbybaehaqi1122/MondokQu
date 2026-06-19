<?php

namespace App\Modules\Admin\Controllers;

use App\Actions\Saas\CreateTenantRoles;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Modules\Admin\Requests\StoreRoleRequest;
use App\Modules\Admin\Requests\UpdateRolePermissionsRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleManagementController extends Controller
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

        $isSuperAdmin = $currentUser?->isSuperAdmin() ?? false;

        if ($isSuperAdmin) {
            $globalRoles = Role::query()
                ->whereNull('tenant_id')
                ->with('permissions')
                ->withCount('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get();

            $tenantRoles = Role::query()
                ->whereNotNull('tenant_id')
                ->with('permissions', 'tenant')
                ->withCount('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get()
                ->groupBy(fn (Role $role) => $role->tenant?->name ?? 'Tenant #'.$role->tenant_id);

            $tenants = Tenant::query()
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('admin.roles', [
                'globalRoles' => $globalRoles,
                'tenantRoles' => $tenantRoles,
                'tenants' => $tenants,
                'permissionGroups' => $permissions,
                'isSuperAdmin' => true,
            ]);
        }

        $roles = Role::query()
            ->forTenant($currentUser?->tenant_id)
            ->with('permissions')
            ->withCount('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('admin.roles', [
            'roles' => $roles,
            'permissionGroups' => $permissions,
            'isSuperAdmin' => false,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Superadmin yang dapat membuat role baru.');
        }

        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
            'tenant_id' => $request->validated('tenant_id') ?: null,
        ]);

        $this->activityLogger->log(
            action: 'role_created',
            actor: $currentUser,
            target: $role,
            description: 'Role baru dibuat.',
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Hak akses baru berhasil dibuat.');
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
            ->with('success', 'Izin untuk role berhasil diperbarui.');
    }

    public function syncTenantRoles(Request $request, Tenant $tenant): RedirectResponse
    {
        if (! $request->user()?->isSuperAdmin()) {
            abort(403);
        }

        app(CreateTenantRoles::class)->handle($tenant);

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Role untuk tenant '.$tenant->name.' berhasil disinkronisasi dari template global.');
    }

    public function syncAllTenants(Request $request): RedirectResponse
    {
        if (! $request->user()?->isSuperAdmin()) {
            abort(403);
        }

        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name']);

        foreach ($tenants as $tenant) {
            app(CreateTenantRoles::class)->handle($tenant);
        }

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Semua role untuk '.$tenants->count().' tenant berhasil disinkronisasi dari template global.');
    }

    public function syncFromTemplate(Request $request, Role $role): RedirectResponse
    {
        if (! $request->user()?->isSuperAdmin()) {
            abort(403);
        }

        if ($role->tenant_id === null) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Role global tidak bisa di-sync dari template.');
        }

        $template = Role::whereNull('tenant_id')
            ->where('name', $role->name)
            ->first();

        if (! $template) {
            return redirect()
                ->route('admin.roles')
                ->with('error', 'Template global untuk role '.$role->name.' tidak ditemukan.');
        }

        $previousPermissions = $role->permissions()->pluck('name')->values()->all();

        $role->syncPermissions($template->permissions);

        $this->activityLogger->log(
            action: 'role_synced_from_template',
            actor: $request->user(),
            target: $role,
            description: 'Permission role diselaraskan dengan template global.',
            properties: [
                'tenant_id' => $role->tenant_id,
                'role_name' => $role->name,
                'permissions' => [
                    'before' => $previousPermissions,
                    'after' => $role->permissions()->pluck('name')->values()->all(),
                ],
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.roles')
            ->with('success', 'Permission role '.$role->name.' berhasil diselaraskan dengan template global.');
    }
}
