<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_sikaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->string('semester');
            $table->enum('sikap_spiritual', ['SB', 'B', 'C', 'K'])->nullable();
            $table->enum('sikap_sosial', ['SB', 'B', 'C', 'K'])->nullable();
            $table->text('deskripsi_spiritual')->nullable();
            $table->text('deskripsi_sosial')->nullable();
            $table->text('catatan_wali')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'santri_id', 'semester'], 'nilai_sikap_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_sikaps');
    }
};
