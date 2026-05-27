<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'action' => fake()->randomElement([
                'santri_created',
                'santri_updated',
                'user_created',
                'user_password_reset',
                'invoice_created',
                'payment_recorded',
            ]),
            'description' => fake()->sentence(),
            'target_type' => fake()->randomElement(['Santri', 'User', 'SantriInvoice']),
            'target_id' => fake()->numberBetween(1, 1000),
            'target_name' => fake()->name(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'properties' => [],
        ];
    }

    /**
     * Indicate the actor.
     */
    public function actedBy(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'actor_id' => $user->id,
            'actor_name' => $user->name,
        ]);
    }
}
