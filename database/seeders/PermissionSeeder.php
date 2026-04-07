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
            'view users',
            'create users',
            'update users',
            'delete users',
            'assign roles',
            'manage system settings',
            'view activity logs',
            'view santri',
            'create santri',
            'update santri',
            'delete santri',
            'manage kamar',
            'create izin',
            'approve izin',
            'view pembayaran',
            'create pembayaran',
            'update pembayaran',
            'edit historical pembayaran',
            'view laporan keuangan',
            'manage tahfidz',
            'manage absensi',
            'manage pelanggaran',
            'view portal wali',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superadmin = Role::findByName('Superadmin', 'web');
        $admin = Role::findByName('Admin', 'web');
        $pengurus = Role::findByName('Pengurus', 'web');
        $bendahara = Role::findByName('Bendahara', 'web');
        $musyrif = Role::findByName('Musyrif/Ustadz', 'web');
        $waliSantri = Role::findByName('Wali Santri', 'web');

        $superadmin->syncPermissions(Permission::whereIn('name', $permissions)->get());
        $admin->syncPermissions(Permission::whereIn('name', [
            'view users',
            'create users',
            'update users',
            'view activity logs',
            'view santri',
            'create santri',
            'update santri',
            'delete santri',
            'manage kamar',
            'create izin',
            'approve izin',
            'view pembayaran',
            'create pembayaran',
            'update pembayaran',
            'view laporan keuangan',
        ])->get());
        $pengurus->syncPermissions(Permission::whereIn('name', [
            'manage kamar',
            'view santri',
            'create santri',
            'update santri',
            'create izin',
            'approve izin',
        ])->get());
        $bendahara->syncPermissions(Permission::whereIn('name', [
            'view pembayaran',
            'create pembayaran',
            'update pembayaran',
            'view laporan keuangan',
        ])->get());
        $musyrif->syncPermissions(Permission::whereIn('name', [
            'view santri',
            'manage tahfidz',
            'manage absensi',
            'manage pelanggaran',
        ])->get());
        $waliSantri->syncPermissions(Permission::whereIn('name', [
            'view portal wali',
        ])->get());
    }
}
