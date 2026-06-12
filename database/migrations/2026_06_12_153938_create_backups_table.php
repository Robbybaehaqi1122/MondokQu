<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('filename');
            $table->string('disk')->default('local');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('type', 20)->default('manual'); // manual, auto, scheduled
            $table->string('status', 20)->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->unsignedInteger('tables_count')->nullable();
            $table->unsignedInteger('total_rows')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
