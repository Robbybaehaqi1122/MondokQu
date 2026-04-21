<?php

namespace Database\Seeders;

use App\Models\Tenant;
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

        $tenant = Tenant::updateOrCreate([
            'slug' => 'pondok-demo',
        ], [
            'name' => 'Pondok Demo',
            'contact_email' => 'admin@example.com',
            'subscription_plan' => config('saas.default_plan', 'trial'),
            'subscription_status' => Tenant::SUBSCRIPTION_ACTIVE,
            'subscription_starts_at' => now(),
            'subscription_ends_at' => now()->addMonth(),
        ]);

        $user = User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'tenant_id' => null,
            'name' => 'Superadmin User',
            'username' => 'superadmin',
            'email' => 'admin@example.com',
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt('password'),
        ]);

        $user->syncRoles(['Superadmin']);

        $tenantAdmin = User::updateOrCreate([
            'email' => 'pondok-admin@example.com',
        ], [
            'tenant_id' => $tenant->id,
            'name' => 'Admin Pondok Demo',
            'username' => 'adminpondok',
            'email' => 'pondok-admin@example.com',
            'status' => User::STATUS_ACTIVE,
            'password' => bcrypt('password'),
        ]);

        $tenantAdmin->syncRoles(['Admin']);

        $tenant->forceFill([
            'owner_id' => $tenantAdmin->id,
        ])->save();
    }
}
