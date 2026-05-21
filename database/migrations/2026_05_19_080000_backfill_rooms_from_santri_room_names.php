<?php

use App\Actions\Room\BackfillRoomsFromSantriRoomNames;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(BackfillRoomsFromSantriRoomNames::class)->handle();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not unlink santri from rooms automatically because backfilled rooms may be edited after migration.
    }
};
