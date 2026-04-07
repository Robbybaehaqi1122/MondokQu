<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Superadmin']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Pengurus']);
        Role::firstOrCreate(['name' => 'Bendahara']);
        Role::firstOrCreate(['name' => 'Musyrif/Ustadz']);
        Role::firstOrCreate(['name' => 'Wali Santri']);
    }
}
