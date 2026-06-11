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
        Schema::create('kesehatan_pemeriksaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->date('tanggal_pemeriksaan');
            $table->string('keluhan');
            $table->string('diagnosis')->nullable();
            $table->text('tindakan')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'tanggal_pemeriksaan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kesehatan_pemeriksaans');
    }
};
