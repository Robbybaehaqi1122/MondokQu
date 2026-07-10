<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->index('target_type');
            $table->index('created_at');
        });

        Schema::table('tahfidz_sessions', function (Blueprint $table): void {
            $table->index('status');
        });

        Schema::table('tahfidz_records', function (Blueprint $table): void {
            $table->index('evaluation');
        });
    }

    public function down(): void
    {
        Schema::table('tahfidz_targets', function (Blueprint $table): void {
            $table->dropIndex(['target_type']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('tahfidz_sessions', function (Blueprint $table): void {
            $table->dropIndex(['status']);
        });

        Schema::table('tahfidz_records', function (Blueprint $table): void {
            $table->dropIndex(['evaluation']);
        });
    }
};
