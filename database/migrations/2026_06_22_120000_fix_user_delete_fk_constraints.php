<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai_santris', function (Blueprint $table): void {
            $table->dropForeign('nilai_santris_input_by_foreign');
            $table->unsignedBigInteger('input_by')->nullable()->change();
            $table->foreign('input_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->dropForeign('tahfidz_targets_created_by_foreign');
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nilai_santris', function (Blueprint $table): void {
            $table->dropForeign('nilai_santris_input_by_foreign');
            DB::statement('ALTER TABLE nilai_santris MODIFY input_by BIGINT UNSIGNED NOT NULL');
            $table->foreign('input_by')->references('id')->on('users');
        });

        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->dropForeign('tahfidz_targets_created_by_foreign');
            DB::statement('ALTER TABLE tahfidz_targets MODIFY created_by BIGINT UNSIGNED NOT NULL');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};
