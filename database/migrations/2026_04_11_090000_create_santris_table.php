<?php

use App\Models\Santri;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('santris', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 50)->unique();
            $table->string('full_name');
            $table->string('gender', 20);
            $table->string('birth_place');
            $table->date('birth_date');
            $table->text('address');
            $table->string('guardian_name');
            $table->string('guardian_phone_number', 30);
            $table->date('entry_date');
            $table->string('status')->default(Santri::STATUS_ACTIVE);
            $table->string('photo_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['gender', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santris');
    }
};
