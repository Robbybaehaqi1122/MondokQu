<?php

use Spatie\Permission\Models\Role;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Role::findOrCreate('Admin', 'web');

    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'phone_number' => '+628123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = auth()->user();
    expect($user)->not->toBeNull();
    expect($user?->hasRole('Admin'))->toBeTrue();
    expect($user?->tenant)->not->toBeNull();
    expect($user?->tenant->owner_id)->toBe($user->id);
});
