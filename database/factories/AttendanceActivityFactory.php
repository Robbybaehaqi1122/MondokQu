<?php

namespace Database\Factories;

use App\Models\AttendanceActivity;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceActivity>
 */
class AttendanceActivityFactory extends Factory
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
            'name' => 'Kegiatan '.fake()->unique()->words(2, true),
            'start_time' => fake()->time('H:i'),
            'end_time' => null,
            'active_days' => [
                AttendanceActivity::DAY_MONDAY,
                AttendanceActivity::DAY_TUESDAY,
                AttendanceActivity::DAY_WEDNESDAY,
                AttendanceActivity::DAY_THURSDAY,
                AttendanceActivity::DAY_FRIDAY,
            ],
            'responsible_user_id' => null,
            'status' => AttendanceActivity::STATUS_ACTIVE,
            'description' => fake()->optional()->sentence(),
            'created_by' => null,
        ];
    }

    /**
     * Attach the activity to a tenant.
     */
    public function forTenant(?Tenant $tenant = null): static
    {
        return $this->state(fn () => [
            'tenant_id' => $tenant?->id ?? Tenant::factory(),
        ]);
    }
}
