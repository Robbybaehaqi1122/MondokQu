<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\PasswordResetLinkRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    protected const GENERIC_RESET_LINK_STATUS = 'Jika email Anda terdaftar di sistem, kami sudah mengirimkan link reset password.';

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('modules.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(PasswordResetLinkRequest $request): RedirectResponse
    {
        Password::sendResetLink(
            $request->safe()->only('email')
        );

        return back()->with('status', self::GENERIC_RESET_LINK_STATUS);
    }
}
