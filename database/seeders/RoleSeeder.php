<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Superadmin', 'tenant_id' => null]);
        Role::firstOrCreate(['name' => 'Admin', 'tenant_id' => null]);
        Role::firstOrCreate(['name' => 'Pengurus', 'tenant_id' => null]);
        Role::firstOrCreate(['name' => 'Bendahara', 'tenant_id' => null]);
        Role::firstOrCreate(['name' => 'Musyrif/Ustadz', 'tenant_id' => null]);
        Role::firstOrCreate(['name' => 'Wali Santri', 'tenant_id' => null]);
    }
}
