<?php

use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kesehatan_rekam_medis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->string('golongan_darah', 5)->nullable();
            $table->text('riwayat_penyakit')->nullable();
            $table->text('alergi_obat')->nullable();
            $table->text('alergi_makanan')->nullable();
            $table->decimal('tinggi_badan', 5, 1)->nullable()->comment('cm');
            $table->decimal('berat_badan', 4, 1)->nullable()->comment('kg');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'santri_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kesehatan_rekam_medis');
    }
};
