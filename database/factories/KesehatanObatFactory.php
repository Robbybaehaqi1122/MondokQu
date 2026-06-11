<?php

namespace Database\Factories;

use App\Models\KesehatanObat;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class KesehatanObatFactory extends Factory
{
    protected $model = KesehatanObat::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'nama_obat' => $this->faker->word(),
            'jenis' => $this->faker->randomElement(['Tablet', 'Sirup', 'Salep', 'Tetes']),
            'stok' => $this->faker->numberBetween(0, 100),
            'satuan' => $this->faker->randomElement(['pcs', 'botol', 'strip', 'tube']),
            'expired_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
        ];
    }
}
