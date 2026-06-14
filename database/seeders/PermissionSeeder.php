<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

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
            'view user details',
            'create users',
            'update users',
            'update user status',
            'reset user passwords',
            'verify user emails',
            'delete users',
            'assign roles',
            'manage system settings',
            'view activity logs',
            'manage activity logs',
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
            'manage komunikasi',
            'manage akademik',
            'manage branding',
            'view portal wali',
            'manage kesehatan',
            'import santri',
            'manage backups',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superadmin = Role::whereNull('tenant_id')->where('name', 'Superadmin')->firstOrFail();
        $admin = Role::whereNull('tenant_id')->where('name', 'Admin')->firstOrFail();
        $pengurus = Role::whereNull('tenant_id')->where('name', 'Pengurus')->firstOrFail();
        $bendahara = Role::whereNull('tenant_id')->where('name', 'Bendahara')->firstOrFail();
        $musyrif = Role::whereNull('tenant_id')->where('name', 'Musyrif/Ustadz')->firstOrFail();
        $waliSantri = Role::whereNull('tenant_id')->where('name', 'Wali Santri')->firstOrFail();

        $superadmin->syncPermissions(Permission::whereIn('name', $permissions)->get());
        $admin->syncPermissions(Permission::whereIn('name', [
            'view users',
            'view user details',
            'create users',
            'update users',
            'update user status',
            'reset user passwords',
            'verify user emails',
            'assign roles',
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
            'manage absensi',
            'manage komunikasi',
            'manage akademik',
            'manage branding',
            'manage kesehatan',
            'import santri',
            'manage backups',
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
            'manage komunikasi',
            'manage akademik',
            'manage kesehatan',
        ])->get());
        $waliSantri->syncPermissions(Permission::whereIn('name', [
            'view portal wali',
        ])->get());
    }
}
