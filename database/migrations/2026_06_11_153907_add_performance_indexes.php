<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri_payments', function (Blueprint $table): void {
            $table->index(['tenant_id', 'santri_invoice_id'], 'santri_payments_tenant_invoice_idx');
        });

        Schema::table('santri_invoices', function (Blueprint $table): void {
            $table->index(['tenant_id', 'santri_id'], 'santri_invoices_tenant_santri_idx');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(['last_login_at'], 'users_last_login_idx');
        });

        Schema::table('nilai_santris', function (Blueprint $table): void {
            $table->index(['tenant_id', 'santri_id'], 'nilai_santris_tenant_santri_idx');
        });

        Schema::table('santri_payment_confirmations', function (Blueprint $table): void {
            $table->index(['tenant_id', 'santri_invoice_id'], 'santri_payment_confirmations_tenant_invoice_idx');
        });
    }

    public function down(): void
    {
        Schema::table('santri_payments', function (Blueprint $table): void {
            $table->dropIndex('santri_payments_tenant_invoice_idx');
        });

        Schema::table('santri_invoices', function (Blueprint $table): void {
            $table->dropIndex('santri_invoices_tenant_santri_idx');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_last_login_idx');
        });

        Schema::table('nilai_santris', function (Blueprint $table): void {
            $table->dropIndex('nilai_santris_tenant_santri_idx');
        });

        Schema::table('santri_payment_confirmations', function (Blueprint $table): void {
            $table->dropIndex('santri_payment_confirmations_tenant_invoice_idx');
        });
    }
};
