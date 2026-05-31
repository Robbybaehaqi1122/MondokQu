<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->enum('direction', ['outgoing', 'incoming'])->comment('outgoing = wali to pondok, incoming = pondok to wali');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'direction']);
            $table->index(['tenant_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
