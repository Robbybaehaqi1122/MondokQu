<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_gelombangs', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable()->unique();
        });

        DB::statement('UPDATE ppdb_gelombangs SET uuid = UUID() WHERE uuid IS NULL');

        Schema::table('ppdb_gelombangs', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_gelombangs', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
