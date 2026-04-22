<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use Throwable;

class SendEmailVerificationNotificationAction
{
    /**
     * Attempt to send an email verification notification safely.
     */
    public function handle(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
