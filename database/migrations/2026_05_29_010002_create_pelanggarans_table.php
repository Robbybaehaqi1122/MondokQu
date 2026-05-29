<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggarans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('pelanggaran_kategoris')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->unsignedSmallInteger('poin');
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'kategori_id']);
            $table->index(['tenant_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggarans');
    }
};
