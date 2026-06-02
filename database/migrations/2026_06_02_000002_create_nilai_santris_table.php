<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_santris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained()->cascadeOnDelete();
            $table->string('semester');
            $table->unsignedTinyInteger('nilai_pengetahuan');
            $table->unsignedTinyInteger('nilai_keterampilan');
            $table->string('notes')->nullable();
            $table->foreignId('input_by')->constrained('users');
            $table->timestamps();

            $table->unique(['tenant_id', 'santri_id', 'mata_pelajaran_id', 'semester'], 'nilai_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_santris');
    }
};
