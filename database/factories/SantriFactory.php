<?php

namespace Database\Factories;

use App\Models\Santri;
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
            'nis' => fake()->unique()->numerify('NIS#######'),
            'full_name' => fake()->name(),
            'gender' => fake()->randomElement(Santri::availableGenders()),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-18 years', '-8 years')->format('Y-m-d'),
            'address' => fake()->address(),
            'guardian_name' => fake()->name(),
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'guardian_phone_number' => fake()->numerify('08##########'),
            'emergency_contact' => fake()->numerify('08##########'),
            'entry_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'entry_year' => (int) fake()->year(),
            'room_name' => 'Asrama '.fake()->randomElement(['A1', 'A2', 'B1', 'B2']),
            'notes' => fake()->optional()->sentence(),
            'status' => Santri::STATUS_ACTIVE,
            'photo_path' => null,
            'created_by' => null,
        ];
    }
}
