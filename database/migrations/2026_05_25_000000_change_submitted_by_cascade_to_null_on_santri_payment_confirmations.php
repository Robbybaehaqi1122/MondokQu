<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri_payment_confirmations', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->foreignId('submitted_by')->nullable()->change();
            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('santri_payment_confirmations', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->foreignId('submitted_by')->change();
            $table->foreign('submitted_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
