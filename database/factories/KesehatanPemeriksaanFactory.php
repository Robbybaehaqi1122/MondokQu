<?php

namespace Database\Factories;

use App\Models\KesehatanPemeriksaan;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KesehatanPemeriksaanFactory extends Factory
{
    protected $model = KesehatanPemeriksaan::class;

    public function definition(): array
    {
        $tenant = Tenant::factory()->create();

        return [
            'tenant_id' => $tenant->id,
            'santri_id' => Santri::factory()->create(['tenant_id' => $tenant->id]),
            'tanggal_pemeriksaan' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'keluhan' => $this->faker->sentence(4),
            'diagnosis' => $this->faker->optional()->word(),
            'tindakan' => $this->faker->optional()->sentence(),
            'dicatat_oleh' => User::factory()->create(['tenant_id' => $tenant->id]),
        ];
    }
}
