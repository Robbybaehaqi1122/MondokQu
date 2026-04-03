<?php

test('public registration screen is disabled', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('public registration endpoint is disabled', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertNotFound();
});
