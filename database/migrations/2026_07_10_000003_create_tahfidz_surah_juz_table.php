<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahfidz_surah_juz', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tahfidz_surah_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('juz');
            $table->timestamps();

            $table->unique(['tahfidz_surah_id', 'juz']);
            $table->index('juz');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahfidz_surah_juz');
    }
};
