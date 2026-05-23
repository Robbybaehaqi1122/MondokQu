<?php

use App\Models\AttendanceSession;
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
        Schema::create('attendance_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_activity_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->string('status', 30)->default(AttendanceSession::STATUS_DRAFT);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'attendance_activity_id', 'session_date'], 'attendance_sessions_unique_daily');
            $table->index(['tenant_id', 'session_date']);
            $table->index(['tenant_id', 'status', 'session_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
