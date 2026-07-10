<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahfidz_sessions', function (Blueprint $table): void {
            $table->softDeletes();
        });

        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('tahfidz_sessions', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });

        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->dropSoftDeletes();
        });
    }
};
