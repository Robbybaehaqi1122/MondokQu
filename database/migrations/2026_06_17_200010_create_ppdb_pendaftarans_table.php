<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gelombang_id')->constrained('ppdb_gelombangs')->restrictOnDelete();
            $table->string('nomor_pendaftaran')->unique();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin');
            $table->text('alamat')->nullable();
            $table->string('no_hp');
            $table->string('email')->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('no_hp_orangtua')->nullable();
            $table->json('berkas')->nullable();
            $table->string('status')->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamp('diterima_at')->nullable();
            $table->timestamp('daftar_ulang_at')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_pendaftarans');
    }
};
