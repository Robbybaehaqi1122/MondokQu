<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('communications')
                ->nullOnDelete();

            $table->boolean('is_replied')
                ->default(false)
                ->after('is_read');

            $table->foreignId('forwarded_from_id')
                ->nullable()
                ->after('is_replied')
                ->constrained('communications')
                ->nullOnDelete();

            $table->index(['parent_id']);
            $table->index(['forwarded_from_id']);
        });
    }

    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['forwarded_from_id']);
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['forwarded_from_id']);
            $table->dropColumn(['parent_id', 'is_replied', 'forwarded_from_id']);
        });
    }
};
