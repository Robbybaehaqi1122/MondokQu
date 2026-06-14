<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memorization_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('musyrif_id')->constrained('users')->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('max_santri')->default(10);
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'musyrif_id']);
            $table->index(['tenant_id', 'day_of_week']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorization_schedules');
    }
};
