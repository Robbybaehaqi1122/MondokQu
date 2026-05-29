<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahfidz_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tahfidz_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surah_id')->constrained('tahfidz_surahs')->cascadeOnDelete();
            $table->unsignedSmallInteger('verse_start');
            $table->unsignedSmallInteger('verse_end');
            $table->string('evaluation', 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tahfidz_session_id']);
            $table->index(['tenant_id', 'surah_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahfidz_records');
    }
};
