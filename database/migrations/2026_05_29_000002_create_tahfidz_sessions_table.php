<?php

use App\Models\TahfidzSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahfidz_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santris')->cascadeOnDelete();
            $table->foreignId('musyrif_id')->constrained('users')->cascadeOnDelete();
            $table->date('session_date');
            $table->string('status', 20)->default(TahfidzSession::STATUS_COMPLETED);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'musyrif_id']);
            $table->index(['tenant_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahfidz_sessions');
    }
};
