<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_exports', function (Blueprint $table): void {
            $table->string('format')->default('csv')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('data_exports', function (Blueprint $table): void {
            $table->dropColumn('format');
        });
    }
};
