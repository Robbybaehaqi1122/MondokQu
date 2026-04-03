<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's permissions and assign them to roles.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'manage users',
            'manage roles',
            'manage santri',
            'manage kamar',
            'manage izin',
            'manage pembayaran',
            'view laporan keuangan',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findByName('Admin', 'web');
        $pengurus = Role::findByName('Pengurus', 'web');
        $bendahara = Role::findByName('Bendahara', 'web');

        $admin->syncPermissions(Permission::whereIn('name', $permissions)->get());
        $pengurus->syncPermissions(Permission::whereIn('name', [
            'manage santri',
            'manage kamar',
            'manage izin',
        ])->get());
        $bendahara->syncPermissions(Permission::whereIn('name', [
            'manage pembayaran',
            'view laporan keuangan',
        ])->get());
    }
}
