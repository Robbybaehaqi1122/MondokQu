<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kesehatan_pemakaian_obats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pemeriksaan_id')->constrained('kesehatan_pemeriksaans')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('kesehatan_obats')->cascadeOnDelete();
            $table->integer('jumlah');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'pemeriksaan_id']);
            $table->index(['tenant_id', 'obat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kesehatan_pemakaian_obats');
    }
};
