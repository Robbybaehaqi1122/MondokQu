<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perpustakaan_kitabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('perpustakaan_kategoris')->restrictOnDelete();
            $table->string('judul');
            $table->string('pengarang')->nullable();
            $table->string('penerbit')->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->string('isbn')->nullable();
            $table->string('lokasi_rak')->nullable();
            $table->unsignedInteger('jumlah_eksemplar')->default(1);
            $table->unsignedInteger('tersedia')->default(1);
            $table->string('kondisi')->default('baik');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'kategori_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perpustakaan_kitabs');
    }
};
