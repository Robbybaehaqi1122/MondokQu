<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Santri>
 */
class SantriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'nis' => fake()->unique()->numerify('NIS#######'),
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(Santri::availableGenders()),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-18 years', '-8 years')->format('Y-m-d'),
            'address' => fake()->address(),
            'guardian_name' => fake()->name(),
            'father_name' => fake()->name('male'),
            'father_phone' => fake()->numerify('08##########'),
            'father_education' => fake()->randomElement(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2']),
            'father_job' => fake()->randomElement(['PNS', 'Guru', 'Petani', 'Wiraswasta', 'Karyawan Swasta']),
            'mother_name' => fake()->name('female'),
            'mother_phone' => fake()->numerify('08##########'),
            'mother_education' => fake()->randomElement(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2']),
            'mother_job' => fake()->randomElement(['Ibu Rumah Tangga', 'Guru', 'PNS', 'Wiraswasta', 'Karyawan Swasta']),
            'guardian_phone_number' => fake()->numerify('08##########'),
            'guardian_relation' => fake()->randomElement(['Paman', 'Kakek', 'Kakak', 'Nenek']),
            'guardian_address' => fake()->address(),
            'emergency_contact' => fake()->numerify('08##########'),
            'entry_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'entry_year' => (int) fake()->year(),
            'notes' => fake()->optional()->sentence(),
            'status' => Santri::STATUS_ACTIVE,
            'photo_path' => null,
            'created_by' => null,
        ];
    }

    /**
     * Attach the santri to a tenant.
     */
    public function forTenant(?Tenant $tenant = null): static
    {
        return $this->state(fn () => [
            'tenant_id' => $tenant?->id ?? Tenant::factory(),
        ]);
    }
}
