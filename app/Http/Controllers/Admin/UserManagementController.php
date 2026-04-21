<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserProfileRequest;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\UserAvatarUploader;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class UserManagementController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger,
        protected UserAvatarUploader $userAvatarUploader
    ) {
    }

    /**
     * Display the user management panel.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $currentUser = $request->user();
        $tenantId = $currentUser && ! $currentUser->isSuperAdmin() ? $currentUser->tenant_id : null;
        $roles = Role::query()
            ->orderBy('name')
            ->get();
        $assignableRoles = $roles->filter(function (Role $role) use ($currentUser) {
            return $currentUser?->can('createWithRole', [User::class, $role->name]) ?? false;
        })->values();

        $baseUsersQuery = User::query()
            ->when($tenantId, fn ($builder) => $builder->where('tenant_id', $tenantId));

        $allUsersCount = (clone $baseUsersQuery)->count();
        $query = trim((string) $request->string('q'));
        $selectedRole = trim((string) $request->string('role'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedVerification = trim((string) $request->string('verification'));
        $selectedTenant = trim((string) $request->string('tenant'));

        $users = (clone $baseUsersQuery)
            ->with(['roles', 'creator', 'tenant'])
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($userQuery) use ($query) {
                    $userQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('phone_number', 'like', "%{$query}%");
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
            ->when(
                $selectedTenant !== '' && ($currentUser?->isSuperAdmin() ?? false),
                fn ($builder) => $builder->whereHas('tenant', fn ($tenantQuery) => $tenantQuery->where('slug', $selectedTenant))
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users', [
            'assignableRoles' => $assignableRoles,
            'canManageRoles' => $currentUser?->isSuperAdmin() ?? false,
            'availableTenants' => $currentUser?->isSuperAdmin()
                ? Tenant::query()->orderBy('name')->get(['id', 'name', 'slug'])
                : collect(),
            'allUsersCount' => $allUsersCount,
            'filters' => [
                'q' => $query,
                'role' => $selectedRole,
                'status' => $selectedStatus,
                'verification' => $selectedVerification,
                'tenant' => $selectedTenant,
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
        $this->authorize('view', $user);

        $currentUser = $request->user();
        $tenantId = $currentUser && ! $currentUser->isSuperAdmin() ? $currentUser->tenant_id : null;
        $user->load(['roles', 'creator', 'tenant']);

        $activityLogs = ActivityLog::query()
            ->with('actor')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
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
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->whereIn('action', ['user_created', 'user_role_updated'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.user-detail', [
            'activityLogs' => $activityLogs,
            'canManageTargetUser' => $currentUser?->can('update', $user) ?? false,
            'canDeleteUser' => $currentUser?->can('delete', $user) ?? false,
            'canManageRoles' => $currentUser?->can('updateRole', $user) ?? false,
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
        $this->authorize('create', User::class);

        $validated = $request->validated();
        $currentUser = $request->user();
        $resolvedTenantId = $currentUser?->isSuperAdmin()
            ? ($validated['tenant_id'] ?? null)
            : $currentUser?->tenant_id;

        $roleAuthorization = Gate::inspect('createWithRole', [User::class, $validated['role']]);
        if ($roleAuthorization->denied()) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => $roleAuthorization->message(),
                ], 'createUser');
        }

        $user = User::query()->create([
            'tenant_id' => $resolvedTenantId,
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'status' => $validated['status'],
            'created_by' => $request->user()?->id,
            'avatar_path' => $this->userAvatarUploader->store($request->file('avatar')),
            'password' => $validated['password'],
        ]);

        $user->syncRoles([$validated['role']]);
        $verificationSent = $this->sendVerificationNotificationSafely($user);
        $this->activityLogger->log(
            action: 'user_created',
            actor: $request->user(),
            target: $user,
            description: 'Membuat user baru beserta role awal.',
            properties: [
                'role' => $validated['role'],
                'status' => $validated['status'],
                'tenant_id' => $resolvedTenantId,
                'tenant_name' => $user->tenant?->name,
                'phone_number' => $validated['phone_number'] ?? null,
            ],
            ipAddress: $request->ip()
        );

        return redirect()
            ->route('admin.users')
            ->with(
                'success',
                $verificationSent
                    ? 'User baru berhasil dibuat dan email verifikasi sudah dikirim.'
                    : 'User baru berhasil dibuat, tetapi email verifikasi belum bisa dikirim. Periksa konfigurasi mailer atau kirim ulang nanti.'
            );
    }

    /**
     * Update the selected role for a user.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $authorization = Gate::inspect('assignRole', [$user, $request->validated('role')]);
        if ($authorization->denied()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $authorization->message());
        }

        $previousRoles = $user->getRoleNames()->implode(', ');
        $user->syncRoles([$request->validated('role')]);

        $this->activityLogger->log(
            action: 'user_role_updated',
            actor: $request->user(),
            target: $user,
            description: 'Role user diperbarui.',
            properties: [
                'roles' => [
                    'before' => $previousRoles ?: null,
                    'after' => $request->validated('role'),
                ],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
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
        $status = $request->validated('status');
        $authorization = Gate::inspect('changeStatus', [$user, $status]);
        if ($authorization->denied()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $authorization->message());
        }

        $previousStatus = $user->status;

        $user->update([
            'status' => $status,
        ]);

        $this->activityLogger->log(
            action: 'user_status_updated',
            actor: $request->user(),
            target: $user,
            description: 'Status user diperbarui.',
            properties: [
                'status' => [
                    'before' => $previousStatus,
                    'after' => $status,
                ],
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
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
        $authorization = Gate::inspect('update', $user);
        if ($authorization->denied()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $authorization->message());
        }

        $validated = $request->validated();

        $emailChanged = $user->email !== $validated['email'];
        $previousEmail = $user->email;
        $previousPhoneNumber = $user->phone_number;
        $previousUsername = $user->username;
        $previousName = $user->name;
        $previousAvatarPath = $user->avatar_path;
        $avatarPath = $this->userAvatarUploader->store($request->file('avatar'), $user->avatar_path);

        $user->forceFill([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'avatar_path' => $avatarPath,
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
                'phone_number' => ['from' => $previousPhoneNumber, 'to' => $validated['phone_number'] ?? null],
                'avatar_path' => ['from' => $previousAvatarPath, 'to' => $avatarPath],
                'email_verification_reset' => $emailChanged,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        if ($previousEmail !== $validated['email']) {
            $this->activityLogger->log(
                action: 'user_email_updated',
                actor: $request->user(),
                target: $user,
                description: 'Email user diperbarui.',
                properties: [
                    'email' => [
                        'before' => $previousEmail,
                        'after' => $validated['email'],
                    ],
                ],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );
        }

        if ($previousPhoneNumber !== ($validated['phone_number'] ?? null)) {
            $this->activityLogger->log(
                action: 'user_phone_updated',
                actor: $request->user(),
                target: $user,
                description: 'Nomor HP user diperbarui.',
                properties: [
                    'phone_number' => [
                        'before' => $previousPhoneNumber,
                        'after' => $validated['phone_number'] ?? null,
                    ],
                ],
                ipAddress: $request->ip(),
                userAgent: $request->userAgent()
            );
        }

        return redirect()
            ->route('admin.users')
            ->with('success', 'Profil user berhasil diperbarui.');
    }

    /**
     * Send a fresh verification email to the user.
     */
    public function resendVerification(User $user): RedirectResponse
    {
        $authorization = Gate::inspect('resendVerification', $user);
        if ($authorization->denied()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $authorization->message());
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Email user ini sudah terverifikasi.');
        }

        if (! $this->sendVerificationNotificationSafely($user)) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'Email verifikasi gagal dikirim. Periksa konfigurasi mailer lalu coba lagi.');
        }

        $this->activityLogger->log(
            action: 'verification_email_resent',
            actor: auth()->user(),
            target: $user,
            description: 'Mengirim ulang email verifikasi.',
            ipAddress: request()?->ip(),
            userAgent: request()?->userAgent()
        );

        return redirect()
            ->route('admin.users')
            ->with('success', 'Email verifikasi berhasil dikirim ulang.');
    }

    /**
     * Attempt to send the verification email without breaking the whole request.
     */
    protected function sendVerificationNotificationSafely(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    /**
     * Mark a user email as verified from the admin panel.
     */
    public function verifyEmail(User $user): RedirectResponse
    {
        $authorization = Gate::inspect('verifyEmail', $user);
        if ($authorization->denied()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $authorization->message());
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
            ipAddress: request()?->ip(),
            userAgent: request()?->userAgent()
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
        $authorization = Gate::inspect('resetPassword', $user);
        if ($authorization->denied()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $authorization->message());
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
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
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
        $authorization = Gate::inspect('delete', $user);
        if ($authorization->denied()) {
            return redirect()
                ->route('admin.users')
                ->with('error', $authorization->message());
        }

        $this->activityLogger->log(
            action: 'user_deleted',
            actor: auth()->user(),
            target: $user,
            description: 'User dihapus dari panel admin.',
            ipAddress: request()?->ip(),
            userAgent: request()?->userAgent()
        );

        $user->delete();

        return redirect()
            ->route('admin.users')
            ->with('success', 'User berhasil dihapus.');
    }
}
