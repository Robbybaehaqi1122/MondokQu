<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ppdb_pengumumans', 'uuid')) {
            Schema::table('ppdb_pengumumans', function ($table) {
                $table->uuid('uuid')->after('id')->nullable()->unique();
            });
        }

        $rows = DB::table('ppdb_pengumumans')->whereNull('uuid')->get();
        foreach ($rows as $row) {
            DB::table('ppdb_pengumumans')->where('id', $row->id)->update([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]);
        }

        Schema::table('ppdb_pengumumans', function ($table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ppdb_pengumumans', function ($table) {
            $table->dropColumn('uuid');
        });
    }
};
