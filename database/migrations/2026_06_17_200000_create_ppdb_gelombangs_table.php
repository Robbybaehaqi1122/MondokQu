<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_gelombangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedInteger('kuota')->nullable();
            $table->decimal('biaya_pendaftaran', 12)->default(0);
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_gelombangs');
    }
};
