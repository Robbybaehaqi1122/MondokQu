<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => null,
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->optional()->phoneNumber(),
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'last_login_at' => null,
            'password_change_required' => false,
            'created_by' => null,
            'avatar_path' => null,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Attach the user to a tenant.
     */
    public function forTenant(?Tenant $tenant = null): static
    {
        return $this->state(fn () => [
            'tenant_id' => $tenant?->id ?? Tenant::factory(),
        ]);
    }
}
