<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Modules\KeuanganQu\Models\CoaAccount;
use Illuminate\Database\Seeder;

class CoaAccountSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found. Skipping COA seeding.');
            return;
        }

        $accounts = [
            // Aset (1)
            ['code' => '1-1000', 'name' => 'Kas', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '1-1100', 'name' => 'Bank', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '1-2000', 'name' => 'Piutang SPP', 'type' => 'aset', 'normal_balance' => 'debit'],
            ['code' => '1-3000', 'name' => 'Perlengkapan', 'type' => 'aset', 'normal_balance' => 'debit'],

            // Kewajiban (2)
            ['code' => '2-1000', 'name' => 'Hutang Usaha', 'type' => 'kewajiban', 'normal_balance' => 'kredit'],
            ['code' => '2-2000', 'name' => 'Hutang Gaji', 'type' => 'kewajiban', 'normal_balance' => 'kredit'],

            // Modal (3)
            ['code' => '3-1000', 'name' => 'Modal Yayasan', 'type' => 'modal', 'normal_balance' => 'kredit'],
            ['code' => '3-2000', 'name' => 'Saldo Laba Ditahan', 'type' => 'modal', 'normal_balance' => 'kredit'],

            // Pendapatan (4)
            ['code' => '4-1000', 'name' => 'Pendapatan SPP', 'type' => 'pendapatan', 'normal_balance' => 'kredit'],
            ['code' => '4-2000', 'name' => 'Pendapatan Makan', 'type' => 'pendapatan', 'normal_balance' => 'kredit'],
            ['code' => '4-3000', 'name' => 'Pendapatan Asrama', 'type' => 'pendapatan', 'normal_balance' => 'kredit'],
            ['code' => '4-4000', 'name' => 'Donasi', 'type' => 'pendapatan', 'normal_balance' => 'kredit'],
            ['code' => '4-5000', 'name' => 'Pendapatan Lain-lain', 'type' => 'pendapatan', 'normal_balance' => 'kredit'],

            // Beban (5)
            ['code' => '5-1000', 'name' => 'Beban Gaji', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '5-2000', 'name' => 'Beban Operasional', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '5-3000', 'name' => 'Beban Makan Santri', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '5-4000', 'name' => 'Beban Listrik & Air', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '5-5000', 'name' => 'Beban Pemeliharaan', 'type' => 'beban', 'normal_balance' => 'debit'],
            ['code' => '5-6000', 'name' => 'Beban Lain-lain', 'type' => 'beban', 'normal_balance' => 'debit'],
        ];

        foreach ($tenants as $tenant) {
            foreach ($accounts as $account) {
                CoaAccount::withoutTenantScope()->firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'code' => $account['code'],
                    ],
                    [
                        'name' => $account['name'],
                        'type' => $account['type'],
                        'normal_balance' => $account['normal_balance'],
                        'description' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
