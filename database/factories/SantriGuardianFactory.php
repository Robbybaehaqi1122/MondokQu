<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\SantriGuardian;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SantriGuardian>
 */
class SantriGuardianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'relationship' => fake()->randomElement(['Ayah', 'Ibu', 'Wali', 'Kakak']),
            'is_primary' => fake()->boolean(80),
        ];
    }

    /**
     * Indicate that the guardian is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate the santri.
     */
    public function forSantri(Santri $santri): static
    {
        return $this->state(fn (array $attributes): array => [
            'tenant_id' => $santri->tenant_id,
            'santri_id' => $santri->id,
        ]);
    }

    /**
     * Indicate the guardian user.
     */
    public function withGuardianUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }
}
