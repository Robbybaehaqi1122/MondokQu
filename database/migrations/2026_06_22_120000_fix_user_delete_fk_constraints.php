<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_santris', function (Blueprint $table): void {
            $table->dropForeign(['input_by']);
            $table->unsignedBigInteger('input_by')->nullable()->change();
            $table->foreign('input_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nilai_santris', function (Blueprint $table): void {
            $table->dropForeign(['input_by']);
            $table->unsignedBigInteger('input_by')->nullable(false)->change();
            $table->foreign('input_by')->references('id')->on('users');
        });

        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};
