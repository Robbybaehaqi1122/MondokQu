<?php

namespace Database\Factories;

use App\Models\SantriPaymentConfirmation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SantriPaymentConfirmation>
 */
class SantriPaymentConfirmationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->numberBetween(1000000, 100000000),
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'payment_method' => fake()->randomElement(['transfer bank', 'tunai']),
            'reference_number' => fake()->optional()->bothify('REF-########'),
            'proof_path' => fake()->imageUrl(),
            'note' => fake()->optional()->sentence(),
            'status' => SantriPaymentConfirmation::STATUS_PENDING,
        ];
    }

    /**
     * Indicate that the confirmation is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SantriPaymentConfirmation::STATUS_APPROVED,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the confirmation is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SantriPaymentConfirmation::STATUS_REJECTED,
            'reviewed_at' => now(),
            'review_note' => fake()->sentence(),
        ]);
    }
}
