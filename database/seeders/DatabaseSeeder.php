<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);

        $user = User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'username' => 'superadmin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('Admin');
    }
}
