<?php

namespace App\Notifications\Concerns;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

trait NotifiesGuardians
{
    protected function notifyGuardians(?Santri $santri, mixed $notification): void
    {
        if (! $santri?->id) {
            return;
        }

        $guardians = $santri->guardians()
            ->where('status', User::STATUS_ACTIVE)
            ->get();

        if ($guardians->isNotEmpty()) {
            Notification::send($guardians, $notification);
        }
    }
}
