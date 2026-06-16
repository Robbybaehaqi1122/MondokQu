<?php

namespace App\Console\Commands;

use App\Models\Communication;
use Illuminate\Console\Command;

class PurgeTrashedCommunications extends Command
{
    protected $signature = 'komunikasi:purge-trash';

    protected $description = 'Permanently delete communications trashed more than 30 days ago';

    public function handle(): void
    {
        $cutoff = now()->subDays(30);

        $count = Communication::query()
            ->onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        $this->info("Purged {$count} trashed communications older than 30 days.");
    }
}
