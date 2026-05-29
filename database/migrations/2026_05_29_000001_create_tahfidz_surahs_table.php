<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahfidz_surahs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('number')->unique();
            $table->string('name');
            $table->string('name_arabic');
            $table->unsignedSmallInteger('verses_count');
            $table->string('juz', 10);
            $table->timestamps();

            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahfidz_surahs');
    }
};
