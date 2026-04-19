<?php

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
        Schema::table('santris', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('guardian_name');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('emergency_contact', 20)->nullable()->after('guardian_phone_number');
            $table->unsignedSmallInteger('entry_year')->nullable()->after('entry_date');
            $table->string('room_name')->nullable()->after('entry_year');
            $table->text('notes')->nullable()->after('room_name');

            $table->index(['entry_year', 'status']);
            $table->index(['room_name', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->dropIndex(['entry_year', 'status']);
            $table->dropIndex(['room_name', 'status']);

            $table->dropColumn([
                'father_name',
                'mother_name',
                'emergency_contact',
                'entry_year',
                'room_name',
                'notes',
            ]);
        });
    }
};
