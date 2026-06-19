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
        if (! Schema::hasColumn('santris', 'uuid')) {
            Schema::table('santris', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        DB::table('santris')->whereNull('uuid')->orderBy('id')->each(function (object $santri) {
            DB::table('santris')
                ->where('id', $santri->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('santris', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->dropIndex(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
