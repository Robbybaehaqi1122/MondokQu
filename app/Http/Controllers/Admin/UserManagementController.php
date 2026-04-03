<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserPasswordRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Display the user management panel.
     */
    public function index(): View
    {
        return view('admin.users', [
            'users' => User::query()
                ->with('roles')
                ->orderBy('name')
                ->get(),
            'roles' => Role::query()
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.users')
            ->with('success', 'User baru berhasil dibuat.');
    }

    /**
     * Update the selected role for a user.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $user->syncRoles([$request->validated('role')]);

        return redirect()
            ->route('admin.users')
            ->with('success', 'Role user berhasil diperbarui.');
    }

    /**
     * Update the password for a user from the admin panel.
     */
    public function updatePassword(UpdateUserPasswordRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('admin.users')
            ->with('success', 'Password user berhasil direset.');
    }

    /**
     * Delete a user from the admin panel.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Akun yang sedang Anda gunakan tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users')
            ->with('success', 'User berhasil dihapus.');
    }
}
