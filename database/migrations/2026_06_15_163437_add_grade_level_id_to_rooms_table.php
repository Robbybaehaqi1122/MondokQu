<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('grade_level_id')
                ->nullable()
                ->after('name')
                ->constrained('grade_levels')
                ->nullOnDelete();

            $table->index(['tenant_id', 'grade_level_id']);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'grade_level_id']);
            $table->dropConstrainedForeignId('grade_level_id');
        });
    }
};
