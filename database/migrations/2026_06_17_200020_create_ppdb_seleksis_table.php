<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_seleksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pendaftaran_id')->constrained('ppdb_pendaftarans')->cascadeOnDelete();
            $table->string('jenis');
            $table->unsignedInteger('nilai')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('hasil')->default('menunggu');
            $table->date('tanggal_seleksi')->nullable();
            $table->foreignId('diuji_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['pendaftaran_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_seleksis');
    }
};
