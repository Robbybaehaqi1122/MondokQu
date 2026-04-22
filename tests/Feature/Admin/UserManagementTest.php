<?php

use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $superadmin = Role::findOrCreate('Superadmin', 'web');
    $adminRole = Role::findOrCreate('Admin', 'web');
    Role::findOrCreate('Pengurus', 'web');
    Role::findOrCreate('Bendahara', 'web');
    Role::findOrCreate('Musyrif/Ustadz', 'web');
    Role::findOrCreate('Wali Santri', 'web');

    $assignRoles = Permission::findOrCreate('assign roles', 'web');
    $manageSystemSettings = Permission::findOrCreate('manage system settings', 'web');
    $viewUsers = Permission::findOrCreate('view users', 'web');
    $viewUserDetails = Permission::findOrCreate('view user details', 'web');
    $createUsers = Permission::findOrCreate('create users', 'web');
    $updateUsers = Permission::findOrCreate('update users', 'web');
    $updateUserStatus = Permission::findOrCreate('update user status', 'web');
    $resetUserPasswords = Permission::findOrCreate('reset user passwords', 'web');
    $verifyUserEmails = Permission::findOrCreate('verify user emails', 'web');
    $deleteUsers = Permission::findOrCreate('delete users', 'web');

    $superadmin->syncPermissions([
        $assignRoles,
        $manageSystemSettings,
        $viewUsers,
        $viewUserDetails,
        $createUsers,
        $updateUsers,
        $updateUserStatus,
        $resetUserPasswords,
        $verifyUserEmails,
        $deleteUsers,
    ]);

    $adminRole->syncPermissions([
        $viewUsers,
        $viewUserDetails,
        $createUsers,
        $updateUsers,
        $updateUserStatus,
        $resetUserPasswords,
        $verifyUserEmails,
    ]);
});

test('admin can view the user management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users'));

    $response->assertOk();
    $response->assertSee('Manajemen User');
});

test('admin can view a user detail page with activity history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create([
        'name' => 'Detail User',
        'username' => 'detailuser',
        'last_login_at' => now()->subHour(),
    ]);
    $user->assignRole('Pengurus');

    ActivityLog::query()->create([
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'user_role_updated',
        'description' => 'Role user diperbarui.',
        'target_type' => User::class,
        'target_id' => $user->id,
        'target_name' => 'Detail User (@detailuser)',
        'properties' => [
            'from' => 'Pengurus',
            'to' => 'Bendahara',
        ],
        'ip_address' => '127.0.0.1',
    ]);

    ActivityLog::query()->create([
        'actor_id' => $user->id,
        'actor_name' => $user->name,
        'action' => 'login_success',
        'description' => 'Login berhasil ke aplikasi.',
        'target_type' => User::class,
        'target_id' => $user->id,
        'target_name' => 'Detail User (@detailuser)',
        'ip_address' => '127.0.0.1',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users.show', $user));

    $response->assertOk();
    $response->assertSee('Detail User');
    $response->assertSee('Riwayat Aktivitas');
    $response->assertSee('Riwayat Perubahan Role');
    $response->assertSee('Login Success');
});

test('admin can search users by name username or email', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    User::factory()->create([
        'name' => 'Ahmad Pencarian',
        'username' => 'ahmad-find',
        'email' => 'ahmad@example.com',
    ]);

    User::factory()->create([
        'name' => 'Budi Lain',
        'username' => 'budi-user',
        'email' => 'budi@example.com',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users', ['q' => 'ahmad-find']));

    $response->assertOk();
    $response->assertSee('Ahmad Pencarian');
    $response->assertDontSee('Budi Lain');
});

test('admin can filter users by role status and verification', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $targetUser = User::factory()->unverified()->create([
        'name' => 'Target Filter',
        'status' => User::STATUS_INACTIVE,
    ]);
    $targetUser->assignRole('Pengurus');

    $otherUser = User::factory()->create([
        'name' => 'User Lain',
        'status' => User::STATUS_ACTIVE,
    ]);
    $otherUser->assignRole('Bendahara');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users', [
            'role' => 'Pengurus',
            'status' => User::STATUS_INACTIVE,
            'verification' => 'unverified',
        ]));

    $response->assertOk();
    $response->assertSee('Target Filter');
    $response->assertDontSee('User Lain');
});

test('user management page is paginated', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    User::factory()->count(15)->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users'));

    $response->assertOk();
    expect($response->viewData('users')->perPage())->toBe(10);
    expect($response->viewData('users')->total())->toBeGreaterThan(10);
});

test('admin can create a user and assign a role from the panel', function () {
    Notification::fake();
    Storage::fake('public');

    $tenant = Tenant::factory()->create([
        'name' => 'Pondok Admin',
        'slug' => 'pondok-admin',
    ]);
    $admin = User::factory()->forTenant($tenant)->create();
    $admin->assignRole('Admin');
    $avatar = function_exists('imagecreatetruecolor')
        ? UploadedFile::fake()->image('avatar-userbaru.png', 400, 400)->size(512)
        : null;

    $payload = [
        'name' => 'User Baru',
        'username' => 'userbaru',
        'email' => 'userbaru@example.com',
        'phone_number' => '081234567890',
        'role' => 'Pengurus',
        'status' => User::STATUS_ACTIVE,
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    if ($avatar) {
        $payload['avatar'] = $avatar;
    }

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), $payload);

    $response->assertRedirect(route('admin.users', absolute: false));

    $user = User::query()->where('email', 'userbaru@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->username)->toBe('userbaru');
    expect($user->status)->toBe(User::STATUS_ACTIVE);
    expect($user->phone_number)->toBe('081234567890');
    expect($user->tenant_id)->toBe($tenant->id);
    if ($avatar) {
        expect($user->avatar_path)->toStartWith('avatars/');
        Storage::disk('public')->assertExists($user->avatar_path);
    } else {
        expect($user->avatar_path)->toBeNull();
    }
    expect($user->created_by)->toBe($admin->id);
    expect($user->hasRole('Pengurus'))->toBeTrue();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('superadmin can assign a tenant when creating a user from the panel', function () {
    Notification::fake();

    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $tenant = Tenant::factory()->create([
        'name' => 'Pondok Al Amanah',
        'slug' => 'pondok-al-amanah',
    ]);

    $response = $this
        ->actingAs($superadmin)
        ->post(route('admin.users.store'), [
            'name' => 'User Tenant',
            'username' => 'usertenant',
            'email' => 'usertenant@example.com',
            'tenant_id' => $tenant->id,
            'role' => 'Pengurus',
            'status' => User::STATUS_ACTIVE,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));

    $user = User::query()->where('email', 'usertenant@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->tenant_id)->toBe($tenant->id);
});

test('admin tenant can not override tenant assignment when creating a user', function () {
    Notification::fake();

    $adminTenant = Tenant::factory()->create([
        'name' => 'Pondok Admin Tenant',
        'slug' => 'pondok-admin-tenant',
    ]);
    $otherTenant = Tenant::factory()->create([
        'name' => 'Pondok Lain',
        'slug' => 'pondok-lain',
    ]);

    $admin = User::factory()->forTenant($adminTenant)->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'User Tenant Aman',
            'username' => 'usertenantaman',
            'email' => 'usertenantaman@example.com',
            'tenant_id' => $otherTenant->id,
            'role' => 'Pengurus',
            'status' => User::STATUS_ACTIVE,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));

    $user = User::query()->where('email', 'usertenantaman@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->tenant_id)->toBe($adminTenant->id);
    expect($user->tenant_id)->not->toBe($otherTenant->id);
});

test('admin can not create a user with admin role from the panel', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users'))
        ->post(route('admin.users.store'), [
            'name' => 'Admin Baru',
            'username' => 'adminbaru',
            'email' => 'adminbaru@example.com',
            'role' => 'Admin',
            'status' => User::STATUS_ACTIVE,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHasErrors(['role'], null, 'createUser');
    expect(User::query()->where('email', 'adminbaru@example.com')->exists())->toBeFalse();
});

test('admin can not create a superadmin from the panel', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users'))
        ->post(route('admin.users.store'), [
            'name' => 'Superadmin Baru',
            'username' => 'superadminbaru',
            'email' => 'superadminbaru@example.com',
            'role' => 'Superadmin',
            'status' => User::STATUS_ACTIVE,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHasErrors(['role'], null, 'createUser');
    expect(User::query()->where('email', 'superadminbaru@example.com')->exists())->toBeFalse();
});

test('admin can update a user role from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Superadmin');

    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-role', $user), [
            'role' => 'Bendahara',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($user->fresh()->hasRole('Bendahara'))->toBeTrue();
});

test('non superadmin admin can not update a user role from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-role', $user), [
            'role' => 'Bendahara',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHas('error');

    expect($user->fresh()->hasRole('Pengurus'))->toBeTrue();
});

test('admin can update a user status from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-status', $user), [
            'status' => User::STATUS_SUSPENDED,
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($user->fresh()->status)->toBe(User::STATUS_SUSPENDED);
});

test('admin can not update superadmin status from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $superadmin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $superadmin->assignRole('Superadmin');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-status', $superadmin), [
            'status' => User::STATUS_SUSPENDED,
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHas('error');
    expect($superadmin->fresh()->status)->toBe(User::STATUS_ACTIVE);
});

test('admin can update a user profile from the panel', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $avatar = function_exists('imagecreatetruecolor')
        ? UploadedFile::fake()->image('avatar-baru.png', 500, 500)->size(768)
        : null;

    $user = User::factory()->create([
        'name' => 'Nama Lama',
        'username' => 'namalama',
        'email' => 'lama@example.com',
        'phone_number' => '081111111111',
        'avatar_path' => null,
        'email_verified_at' => now(),
    ]);

    $payload = [
        'name' => 'Nama Baru',
        'username' => 'namabaru',
        'email' => 'baru@example.com',
        'phone_number' => '082222222222',
    ];

    if ($avatar) {
        $payload['avatar'] = $avatar;
    }

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update', $user), $payload);

    $response->assertRedirect(route('admin.users', absolute: false));

    $user = $user->fresh();

    expect($user->name)->toBe('Nama Baru');
    expect($user->username)->toBe('namabaru');
    expect($user->email)->toBe('baru@example.com');
    expect($user->phone_number)->toBe('082222222222');
    if ($avatar) {
        expect($user->avatar_path)->toStartWith('avatars/');
        Storage::disk('public')->assertExists($user->avatar_path);
    } else {
        expect($user->avatar_path)->toBeNull();
    }
    expect($user->email_verified_at)->toBeNull();

    $actions = ActivityLog::query()
        ->where('target_id', $user->id)
        ->pluck('action')
        ->all();

    expect($actions)->toContain('user_profile_updated');
    expect($actions)->toContain('user_email_updated');
    expect($actions)->toContain('user_phone_updated');
});

test('admin can not update a superadmin profile from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $superadmin = User::factory()->create([
        'name' => 'Super Lama',
        'username' => 'superlama',
        'email' => 'superlama@example.com',
    ]);
    $superadmin->assignRole('Superadmin');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update', $superadmin), [
            'name' => 'Super Baru',
            'username' => 'superbaru',
            'email' => 'superbaru@example.com',
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHas('error');

    $superadmin = $superadmin->fresh();
    expect($superadmin->name)->toBe('Super Lama');
    expect($superadmin->username)->toBe('superlama');
    expect($superadmin->email)->toBe('superlama@example.com');
});

test('admin can not deactivate their own account from the panel', function () {
    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-status', $admin), [
            'status' => User::STATUS_INACTIVE,
        ]);

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($admin->fresh()->status)->toBe(User::STATUS_ACTIVE);
});

test('admin can reset a user password from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create([
        'password' => 'password',
    ]);
    $previousRememberToken = $user->remember_token;

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-password', $user));

    $response->assertRedirect(route('admin.users', absolute: false));
    expect(Hash::check(config('auth.default_user_password'), $user->fresh()->password))->toBeTrue();
    expect($user->fresh()->password_change_required)->toBeTrue();
    expect($user->fresh()->remember_token)->not->toBe($previousRememberToken);
});

test('admin can not reset superadmin password from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $superadmin = User::factory()->create([
        'password' => 'old-password',
    ]);
    $superadmin->assignRole('Superadmin');
    $originalPassword = $superadmin->password;

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update-password', $superadmin));

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHas('error');
    expect($superadmin->fresh()->password)->toBe($originalPassword);
    expect($superadmin->fresh()->password_change_required)->toBeFalse();
});

test('admin can resend a verification email from the panel', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->unverified()->create();

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.resend-verification', $user));

    $response->assertRedirect(route('admin.users', absolute: false));
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('admin can not resend a superadmin verification email from the panel', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $superadmin = User::factory()->unverified()->create();
    $superadmin->assignRole('Superadmin');

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.resend-verification', $superadmin));

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHas('error');
    Notification::assertNothingSent();
});

test('admin can verify a user email manually from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->unverified()->create();

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.verify-email', $user));

    $response->assertRedirect(route('admin.users', absolute: false));
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('admin can not verify a superadmin email manually from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $superadmin = User::factory()->unverified()->create();
    $superadmin->assignRole('Superadmin');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.verify-email', $superadmin));

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHas('error');
    expect($superadmin->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('superadmin can delete another user from the panel', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($superadmin)
        ->delete(route('admin.users.destroy', $user));

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($user->fresh())->toBeNull();
});

test('admin can not delete another user from the panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($admin)
        ->delete(route('admin.users.destroy', $user));

    $response->assertRedirect(route('admin.users', absolute: false));
    $response->assertSessionHas('error');

    expect($user->fresh())->not->toBeNull();
});

test('superadmin can not delete their own account from the panel', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    $response = $this
        ->actingAs($superadmin)
        ->delete(route('admin.users.destroy', $superadmin));

    $response->assertRedirect(route('admin.users', absolute: false));

    expect($superadmin->fresh())->not->toBeNull();
});

test('non admin users can not access the user management page', function () {
    $user = User::factory()->create();
    $user->assignRole('Pengurus');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.users'));

    $response->assertForbidden();
});

test('superadmin can view the role management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Superadmin');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.roles'));

    $response->assertOk();
    $response->assertSee('Manajemen Role');
});

test('admin without permission can not access the role management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.roles'));

    $response->assertForbidden();
});

test('superadmin can view the permission management page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Superadmin');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.permissions'));

    $response->assertOk();
    $response->assertSee('Permission Management');
});
