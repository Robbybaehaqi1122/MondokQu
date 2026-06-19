<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri_payments', function (Blueprint $table) {
            $table->foreignId('payment_account_id')->nullable()->after('payment_method')->constrained('payment_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('santri_payments', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropColumn('payment_account_id');
        });
    }
};
