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
            $table->string('father_phone', 20)->nullable()->after('father_name');
            $table->string('father_education', 100)->nullable()->after('father_phone');
            $table->string('father_job', 100)->nullable()->after('father_education');
            $table->string('mother_phone', 20)->nullable()->after('mother_name');
            $table->string('mother_education', 100)->nullable()->after('mother_phone');
            $table->string('mother_job', 100)->nullable()->after('mother_education');
            $table->string('guardian_relation', 50)->nullable()->after('guardian_phone_number');
            $table->text('guardian_address')->nullable()->after('guardian_relation');
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->dropColumn([
                'father_phone',
                'father_education',
                'father_job',
                'mother_phone',
                'mother_education',
                'mother_job',
                'guardian_relation',
                'guardian_address',
            ]);
        });
    }
};
