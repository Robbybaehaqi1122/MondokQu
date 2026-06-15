<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attitude_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->string('semester');
            $table->enum('aspect', ['spiritual', 'sosial']);
            $table->string('aspect_name');
            $table->enum('predicate', ['SB', 'B', 'C', 'K'])->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'santri_id', 'semester', 'aspect', 'aspect_name'], 'attitude_grade_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attitude_grades');
    }
};
