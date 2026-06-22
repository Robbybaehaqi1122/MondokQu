<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_gelombangs', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable()->unique();
        });

        DB::table('ppdb_gelombangs')->whereNull('uuid')->each(function ($row) {
            DB::table('ppdb_gelombangs')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
        });

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
