<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanction_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sanction_type'); // warning, parent_call, guidance, suspension, dismissal
            $table->unsignedSmallInteger('min_points');
            $table->unsignedSmallInteger('max_points')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'min_points']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanction_thresholds');
    }
};
