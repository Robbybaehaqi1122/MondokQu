<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_asets')->restrictOnDelete();
            $table->foreignId('lokasi_id')->constrained('lokasi_asets')->restrictOnDelete();
            $table->string('kode_aset', 30);
            $table->string('name', 200);
            $table->string('merk', 200)->nullable();
            $table->year('tahun_perolehan')->nullable();
            $table->bigInteger('harga_perolehan')->default(0);
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->default('baik');
            $table->string('qr_code', 255)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'kode_aset']);
            $table->index(['tenant_id', 'kategori_id']);
            $table->index(['tenant_id', 'lokasi_id']);
            $table->index(['tenant_id', 'kondisi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asets');
    }
};
