<?php

namespace Database\Factories;

use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SantriPayment>
 */
class SantriPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invoice = SantriInvoice::factory()->create();

        return [
            'tenant_id' => $invoice->tenant_id,
            'santri_invoice_id' => $invoice->id,
            'santri_id' => $invoice->santri_id,
            'paid_at' => now(),
            'amount' => 25000000,
            'payment_method' => fake()->randomElement(SantriPayment::paymentMethods()),
            'reference_number' => fake()->optional()->numerify('REF#######'),
            'note' => fake()->optional()->sentence(),
            'recorded_by' => null,
        ];
    }

    /**
     * Attach the payment to an invoice.
     */
    public function forInvoice(SantriInvoice $invoice): static
    {
        return $this->state(fn () => [
            'tenant_id' => $invoice->tenant_id,
            'santri_invoice_id' => $invoice->id,
            'santri_id' => $invoice->santri_id,
        ]);
    }
}
