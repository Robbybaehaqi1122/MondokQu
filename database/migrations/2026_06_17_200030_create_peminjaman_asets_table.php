<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_asets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('aset_id')->constrained('asets')->cascadeOnDelete();
            $table->string('peminjam', 200);
            $table->string('role_peminjam', 100)->nullable();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali')->nullable();
            $table->text('tujuan')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'aset_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'tanggal_pinjam']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_asets');
    }
};
