<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\SendEmailVerificationNotificationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(
        Request $request,
        SendEmailVerificationNotificationAction $sendVerificationNotification
    ): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if (! $sendVerificationNotification->handle($request->user())) {
            return back()->withErrors([
                'email' => 'Link verifikasi belum bisa dikirim sekarang. Periksa konfigurasi mailer lalu coba lagi.',
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
