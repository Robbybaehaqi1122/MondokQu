<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santris', function (Blueprint $table) {
            $table->string('nisn', 50)->nullable()->after('nis');
            $table->index('nisn');
        });

        Schema::create('data_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('status'); // pending, processing, completed, failed
            $table->string('original_filename')->nullable();
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->json('summary')->nullable(); // {"success": 10, "failed": 2, "errors": [...]}
            $table->json('filters')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->text('failure_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_imports');

        Schema::table('santris', function (Blueprint $table) {
            $table->dropIndex(['nisn']);
            $table->dropColumn('nisn');
        });
    }
};
