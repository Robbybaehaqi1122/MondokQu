<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SantriInvoice>
 */
class SantriInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::factory()->create();
        $santri = Santri::factory()->forTenant($tenant)->create();

        return [
            'tenant_id' => $tenant->id,
            'santri_id' => $santri->id,
            'invoice_number' => 'INV-'.fake()->unique()->numerify('########'),
            'title' => 'SPP Bulanan',
            'period_month' => (int) now()->month,
            'period_year' => (int) now()->year,
            'due_date' => now()->addDays(10)->toDateString(),
            'amount' => 50000000,
            'paid_amount' => 0,
            'status' => SantriInvoice::STATUS_PENDING,
            'notes' => null,
            'created_by' => null,
        ];
    }

    /**
     * Attach the invoice to a tenant.
     */
    public function forTenant(?Tenant $tenant = null): static
    {
        $tenant ??= Tenant::factory()->create();

        return $this->state(fn () => [
            'tenant_id' => $tenant->id,
            'santri_id' => Santri::factory()->forTenant($tenant),
        ]);
    }

    /**
     * Attach the invoice to a santri.
     */
    public function forSantri(Santri $santri): static
    {
        return $this->state(fn () => [
            'tenant_id' => $santri->tenant_id,
            'santri_id' => $santri->id,
        ]);
    }

    /**
     * Mark the invoice as fully paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['amount'] ?? 50000000,
            'status' => SantriInvoice::STATUS_PAID,
        ]);
    }
}
