<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sanction_threshold_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('total_points_at_time');
            $table->timestamp('triggered_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['santri_id', 'sanction_threshold_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanction_logs');
    }
};
