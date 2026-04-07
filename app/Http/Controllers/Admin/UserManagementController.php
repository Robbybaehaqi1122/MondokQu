<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserProfileRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Display the user management panel.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $roles = Role::query()
            ->orderBy('name')
            ->get();
        $assignableRoles = $roles->filter(function (Role $role) use ($currentUser) {
            return $this->canAssignRole($currentUser, $role->name);
        })->values();

        $allUsersCount = User::query()->count();
        $query = trim((string) $request->string('q'));
        $selectedRole = trim((string) $request->string('role'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedVerification = trim((string) $request->string('verification'));

        $users = User::query()
            ->with(['roles', 'creator'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($userQuery) use ($query) {
                    $userQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->when($selectedRole !== '', function ($builder) use ($selectedRole) {
                $builder->whereHas('roles', function ($roleQuery) use ($selectedRole) {
                    $roleQuery->where('name', $selectedRole);
                });
            })
            ->when($selectedStatus !== '', function ($builder) use ($selectedStatus) {
                $builder->where('status', $selectedStatus);
            })
            ->when($selectedVerification === 'verified', function ($builder) {
                $builder->whereNotNull('email_verified_at');
            })
            ->when($selectedVerification === 'unverified', function ($builder) {
                $builder->whereNull('email_verified_at');
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users', [
            'assignableRoles' => $assignableRoles,
            'canManageRoles' => $currentUser?->isSuperAdmin() ?? false,
            'allUsersCount' => $allUsersCount,
            'filters' => [
                'q' => $query,
                'role' => $selectedRole,
                'status' => $selectedStatus,
                'verification' => $selectedVerification,
            ],
            'statuses' => User::availableStatuses(),
            'roles' => $roles,
            'users' => $users,
        ]);
    }

    /**
     * Display the detail page for a user.
     */
    public function show(Request $request, User $user): View
    {
        $currentUser = $request->user();
        $user->load(['roles', 'creator']);

        $activityLogs = ActivityLog::query()
            ->with('actor')
            ->where(function ($query) use ($user) {
                $query
                    ->where(function ($targetQuery) use ($user) {
                        $targetQuery
                            ->where('target_type', User::class)
                            ->where('target_id', $user->id);
                    })
                    ->orWhere('actor_id', $user->id);
            })
            ->latest()
            ->limit(12)
            ->get();

        $roleHistory = ActivityLog::query()
            ->with('actor')
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->whereIn('action', ['user_created', 'user_role_updated'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.user-detail', [
            'activityLogs' => $activityLogs,
            'canManageTargetUser' => $currentUser?->isSuperAdmin() || ! $user->isSuperAdmin(),
            'canDeleteUser' => $currentUser?->isSuperAdmin() && $currentUser->id !== $user->id,
            'canManageRoles' => $currentUser?->isSuperAdmin() ?? false,
            'roleHistory' => $roleHistory,
            'roles' => Role::query()->orderBy('name')->get(),
            'statuses' => User::availableStatuses(),
            'userDetail' => $user,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! $this->canAssignRole($request->user(), $validated['role'])) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'Hanya Superadmin yang dapat membuat user dengan role Admin atau Superadmin.',
                ], 'createUser');
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'status' => $validated['status'],
            'created_by' => $request->user()?->id,
            'password' => $validated['password'],
        ]);

        $user->syncRoles([$validated['role']]);
        $user->sendEmailVerificationNotification();
        $this->activityLogger->log(
            action: 'user_created',
            actor: $request->user(),
            target: $user,
            description: 'Membuat user baru beserta role awal.',
            properties: [
                'role' => $validated['role'],
                'status' => $validated['status'],
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'User baru berhasil dibuat dan email verifikasi sudah dikirim.');
    }

    /**
     * Update the selected role for a user.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        if (! $request->user()?->isSuperAdmin()) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Perubahan role hanya dapat dilakukan oleh Superadmin.');
        }

        $previousRoles = $user->getRoleNames()->implode(', ');
        $user->syncRoles([$request->validated('role')]);

        $this->activityLogger->log(
            action: 'user_role_updated',
            actor: $request->user(),
            target: $user,
            description: 'Role user diperbarui.',
            properties: [
                'from' => $previousRoles ?: null,
                'to' => $request->validated('role'),
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Role user berhasil diperbarui.');
    }

    /**
     * Update the selected status for a user.
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): RedirectResponse
    {
        if ($response = $this->denyIfProtectedTarget($request->user(), $user, 'Akun Superadmin hanya dapat diubah oleh Superadmin.')) {
            return $response;
        }

        $status = $request->validated('status');
        $previousStatus = $user->status;

        if (auth()->id() === $user->id && $status !== User::STATUS_ACTIVE) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Akun yang sedang Anda gunakan harus tetap aktif.');
        }

        $user->update([
            'status' => $status,
        ]);

        $this->activityLogger->log(
            action: 'user_status_updated',
            actor: $request->user(),
            target: $user,
            description: 'Status user diperbarui.',
            properties: [
                'from' => $previousStatus,
                'to' => $status,
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Status user berhasil diperbarui.');
    }

    /**
     * Update the profile data for a user from the admin panel.
     */
    public function updateProfile(UpdateUserProfileRequest $request, User $user): RedirectResponse
    {
        if ($response = $this->denyIfProtectedTarget($request->user(), $user, 'Akun Superadmin hanya dapat diubah oleh Superadmin.')) {
            return $response;
        }

        $validated = $request->validated();

        $emailChanged = $user->email !== $validated['email'];
        $previousEmail = $user->email;
        $previousUsername = $user->username;
        $previousName = $user->name;

        $user->forceFill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ])->save();

        $this->activityLogger->log(
            action: 'user_profile_updated',
            actor: $request->user(),
            target: $user,
            description: 'Profil user diperbarui dari panel admin.',
            properties: [
                'name' => ['from' => $previousName, 'to' => $validated['name']],
                'username' => ['from' => $previousUsername, 'to' => $validated['username']],
                'email' => ['from' => $previousEmail, 'to' => $validated['email']],
                'email_verification_reset' => $emailChanged,
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Profil user berhasil diperbarui.');
    }

    /**
     * Send a fresh verification email to the user.
     */
    public function resendVerification(User $user): RedirectResponse
    {
        if ($response = $this->denyIfProtectedTarget(auth()->user(), $user, 'Akun Superadmin hanya dapat diubah oleh Superadmin.')) {
            return $response;
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Email user ini sudah terverifikasi.');
        }

        $user->sendEmailVerificationNotification();

        $this->activityLogger->log(
            action: 'verification_email_resent',
            actor: auth()->user(),
            target: $user,
            description: 'Mengirim ulang email verifikasi.',
            ipAddress: request()?->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Email verifikasi berhasil dikirim ulang.');
    }

    /**
     * Mark a user email as verified from the admin panel.
     */
    public function verifyEmail(User $user): RedirectResponse
    {
        if ($response = $this->denyIfProtectedTarget(auth()->user(), $user, 'Akun Superadmin hanya dapat diubah oleh Superadmin.')) {
            return $response;
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Email user ini sudah terverifikasi.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $this->activityLogger->log(
            action: 'email_verified_manual',
            actor: auth()->user(),
            target: $user,
            description: 'Email user ditandai terverifikasi secara manual.',
            ipAddress: request()?->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Email user berhasil ditandai sebagai terverifikasi.');
    }

    /**
     * Update the password for a user from the admin panel.
     */
    public function updatePassword(Request $request, User $user): RedirectResponse
    {
        if ($response = $this->denyIfProtectedTarget($request->user(), $user, 'Password Superadmin hanya dapat direset oleh Superadmin.')) {
            return $response;
        }

        $defaultPassword = config('auth.default_user_password');

        $user->update([
            'password' => $defaultPassword,
            'password_change_required' => true,
        ]);

        $this->activityLogger->log(
            action: 'user_password_reset',
            actor: $request->user(),
            target: $user,
            description: 'Password user direset ke password default dan wajib diganti saat login berikutnya.',
            properties: [
                'password_change_required' => true,
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Password user berhasil direset ke default. User wajib menggantinya saat login berikutnya.');
    }

    /**
     * Delete a user from the admin panel.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (! auth()->user()?->isSuperAdmin()) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Hanya Superadmin yang dapat menghapus user.');
        }

        if (auth()->id() === $user->id) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Akun yang sedang Anda gunakan tidak dapat dihapus.');
        }

        $this->activityLogger->log(
            action: 'user_deleted',
            actor: auth()->user(),
            target: $user,
            description: 'User dihapus dari panel admin.',
            ipAddress: request()?->ip()
        );

        $user->delete();

        return redirect()
            ->route('admin.users')
            ->with('success', 'User berhasil dihapus.');
    }

    protected function canAssignRole(?User $actor, string $roleName): bool
    {
        if (! $actor) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        return ! in_array($roleName, ['Superadmin', 'Admin'], true);
    }

    protected function denyIfProtectedTarget(?User $actor, User $target, string $message): ?RedirectResponse
    {
        if ($target->isSuperAdmin() && ! $actor?->isSuperAdmin()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $message);
        }

        return null;
    }
}
