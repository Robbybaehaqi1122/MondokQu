<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'Asrama '.fake()->unique()->bothify('?#'),
            'capacity' => fake()->numberBetween(10, 40),
            'status' => Room::STATUS_ACTIVE,
            'description' => fake()->optional()->sentence(),
            'created_by' => null,
        ];
    }

    /**
     * Attach the room to a tenant.
     */
    public function forTenant(?Tenant $tenant = null): static
    {
        return $this->state(fn () => [
            'tenant_id' => $tenant?->id ?? Tenant::factory(),
        ]);
    }
}
