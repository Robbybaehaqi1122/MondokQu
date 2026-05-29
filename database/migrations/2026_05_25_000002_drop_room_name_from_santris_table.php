<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table): void {
            $table->dropIndex(['room_name', 'status']);
            $table->dropColumn('room_name');
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table): void {
            $table->string('room_name')->nullable()->after('entry_year');
        });

        Schema::table('santris', function (Blueprint $table): void {
            $table->index(['room_name', 'status']);
        });
    }
};
