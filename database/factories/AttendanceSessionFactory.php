<?php

namespace Database\Factories;

use App\Models\AttendanceActivity;
use App\Models\AttendanceSession;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSession>
 */
class AttendanceSessionFactory extends Factory
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
            'attendance_activity_id' => AttendanceActivity::factory(),
            'session_date' => now()->toDateString(),
            'status' => AttendanceSession::STATUS_DRAFT,
            'notes' => fake()->optional()->sentence(),
            'created_by' => null,
        ];
    }

    /**
     * Attach the session to a tenant.
     */
    public function forTenant(?Tenant $tenant = null): static
    {
        return $this->state(function () use ($tenant): array {
            $tenant ??= Tenant::factory()->create();

            return [
                'tenant_id' => $tenant->id,
                'attendance_activity_id' => AttendanceActivity::factory()->forTenant($tenant),
            ];
        });
    }

    /**
     * Attach the session to an existing activity.
     */
    public function forActivity(AttendanceActivity $activity): static
    {
        return $this->state(fn () => [
            'tenant_id' => $activity->tenant_id,
            'attendance_activity_id' => $activity->id,
        ]);
    }
}
