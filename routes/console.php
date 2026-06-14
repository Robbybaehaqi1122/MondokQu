<?php

use App\Models\Backup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schedule;

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

    Role::findOrCreate('Superadmin', 'web');

    $user = User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'status' => User::STATUS_ACTIVE,
            'password' => Hash::make($password),
        ]
    );

    $user->syncRoles(['Superadmin']);

    $this->info('Akun superadmin berhasil dibuat atau diperbarui.');
    $this->line('Login dengan username: '.$user->username);
    $this->line('atau email: '.$user->email);

    return 0;
})->purpose('Membuat akun superadmin internal');

Schedule::command('saas:expire-subscriptions')
    ->dailyAt('00:30')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('exports:prune')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('backup:tenant --all')
    ->weeklyOn(0, '02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/backup-schedule.log'));

Schedule::call(function () {
    Backup::pruneOlderThan(config('backups.retention_days', 30));
})->name('backups:prune')->dailyAt('03:00')->withoutOverlapping();
