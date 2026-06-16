<?php

namespace Database\Seeders;

use App\Models\SanctionThreshold;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SanctionThresholdSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            return;
        }

        foreach ($tenants as $tenant) {
            foreach (SanctionThreshold::defaultThresholds() as $threshold) {
                SanctionThreshold::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'min_points' => $threshold['min_points']],
                    array_merge($threshold, ['tenant_id' => $tenant->id]),
                );
            }
        }

        $this->command?->info('Default sanction thresholds seeded for all tenants.');
    }
}
