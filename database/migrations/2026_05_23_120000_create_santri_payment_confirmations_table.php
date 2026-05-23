<?php

use App\Models\SantriPaymentConfirmation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('santri_payment_confirmations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_invoice_id')->constrained('santri_invoices')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamp('paid_at');
            $table->string('payment_method', 50)->default('transfer bank');
            $table->string('reference_number', 100)->nullable();
            $table->string('proof_path');
            $table->text('note')->nullable();
            $table->string('status', 30)->default(SantriPaymentConfirmation::STATUS_PENDING);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['submitted_by', 'created_at']);
            $table->index(['santri_invoice_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_payment_confirmations');
    }
};
