<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:create-admin', function () {
    $name = $this->ask('Nama lengkap');
    $username = $this->ask('Username');
    $email = $this->ask('Email');
    $password = $this->secret('Password');

    if (! $name || ! $username || ! $email || ! $password) {
        $this->error('Semua field wajib diisi.');

        return 1;
    }

    Role::findOrCreate('Admin', 'web');

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
        ]
    );

    $user->syncRoles(['Admin']);

    $this->info('Akun admin berhasil dibuat atau diperbarui.');
    $this->line('Login dengan username: '.$user->username);
    $this->line('atau email: '.$user->email);

    return 0;
})->purpose('Membuat akun admin/superadmin internal');
