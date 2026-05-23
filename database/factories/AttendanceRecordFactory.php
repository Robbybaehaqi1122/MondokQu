<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Santri;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
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
            'attendance_session_id' => AttendanceSession::factory(),
            'santri_id' => Santri::factory(),
            'status' => fake()->randomElement(AttendanceRecord::availableStatuses()),
            'notes' => fake()->optional()->sentence(),
            'recorded_by' => null,
            'recorded_at' => now(),
        ];
    }

    /**
     * Attach the record to an existing attendance session and santri.
     */
    public function forSessionAndSantri(AttendanceSession $session, Santri $santri): static
    {
        return $this->state(fn () => [
            'tenant_id' => $session->tenant_id,
            'attendance_session_id' => $session->id,
            'santri_id' => $santri->id,
        ]);
    }
}
