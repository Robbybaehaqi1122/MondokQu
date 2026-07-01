<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table): void {
            $table->string('barcode', 20)->unique()->nullable()->after('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table): void {
            $table->dropColumn('barcode');
        });
    }
};
