<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kesehatan_obats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nama_obat');
            $table->string('jenis', 50)->nullable();
            $table->integer('stok')->default(0);
            $table->string('satuan', 20)->default('pcs');
            $table->date('expired_date')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'nama_obat']);
            $table->index(['tenant_id', 'expired_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kesehatan_obats');
    }
};
