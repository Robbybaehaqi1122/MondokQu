<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Resize columns that are too small to hold Laravel's encrypted output

        Schema::table('santris', function (Blueprint $table) {
            $table->text('father_phone')->nullable()->change();
            $table->text('mother_phone')->nullable()->change();
            $table->text('guardian_phone_number')->nullable()->change();
            $table->text('emergency_contact')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('phone_number')->nullable()->change();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->text('contact_phone_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->string('father_phone', 20)->nullable()->change();
            $table->string('mother_phone', 20)->nullable()->change();
            $table->string('guardian_phone_number', 30)->nullable()->change();
            $table->string('emergency_contact', 20)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 30)->nullable()->change();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('contact_phone_number', 30)->nullable()->change();
        });
    }
};
