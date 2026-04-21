<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Pondok '.fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(10, 9999)),
            'contact_email' => fake()->companyEmail(),
            'contact_phone_number' => fake()->numerify('08##########'),
            'subscription_plan' => 'trial',
            'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
            'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
            'subscription_starts_at' => null,
            'subscription_ends_at' => null,
            'grace_ends_at' => null,
            'owner_id' => null,
        ];
    }

    /**
     * Mark the tenant as having an active paid subscription.
     */
    public function activeSubscription(): static
    {
        return $this->state(fn () => [
            'subscription_plan' => 'basic',
            'subscription_status' => Tenant::SUBSCRIPTION_ACTIVE,
            'trial_ends_at' => now()->subDay(),
            'subscription_starts_at' => now()->subDays(7),
            'subscription_ends_at' => now()->addMonth(),
            'grace_ends_at' => null,
        ]);
    }

    /**
     * Mark the tenant as being in grace period.
     */
    public function onGracePeriod(): static
    {
        return $this->state(fn () => [
            'subscription_plan' => 'basic',
            'subscription_status' => Tenant::SUBSCRIPTION_GRACE,
            'trial_ends_at' => now()->subDays(14),
            'subscription_starts_at' => now()->subMonths(2),
            'subscription_ends_at' => now()->subDay(),
            'grace_ends_at' => now()->addDays((int) config('saas.grace_days', 5)),
        ]);
    }

    /**
     * Mark the tenant as expired and blocked.
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'subscription_plan' => 'basic',
            'subscription_status' => Tenant::SUBSCRIPTION_EXPIRED,
            'trial_ends_at' => now()->subDays(20),
            'subscription_starts_at' => now()->subMonths(2),
            'subscription_ends_at' => now()->subDays(10),
            'grace_ends_at' => now()->subDay(),
        ]);
    }
}
