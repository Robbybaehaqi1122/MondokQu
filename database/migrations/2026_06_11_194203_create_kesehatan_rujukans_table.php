<?php

use App\Models\KesehatanPemeriksaan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kesehatan_rujukans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pemeriksaan_id')->constrained('kesehatan_pemeriksaans')->cascadeOnDelete();
            $table->string('tempat_rujukan');
            $table->string('diagnosis_dokter')->nullable();
            $table->date('tanggal_rujuk');
            $table->date('tanggal_kembali')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'pemeriksaan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kesehatan_rujukans');
    }
};
