<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\AttemptLoginAction;
use App\Modules\Auth\Actions\DetermineDashboardRouteAction;
use App\Modules\Auth\Requests\LoginRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('modules.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(
        LoginRequest $request,
        AttemptLoginAction $attemptLogin,
        DetermineDashboardRouteAction $determineDashboardRoute
    ): RedirectResponse {
        $attemptLogin->handle($request, $request->validated(), $request->boolean('remember'));

        return redirect()->intended(
            $determineDashboardRoute->handle($request->user())
        )->with('success', 'Login berhasil. Selamat datang kembali.');
    }

    /**
     * Check whether a username or email exists for live login feedback.
     */
    public function checkIdentity(Request $request): JsonResponse
    {
        $login = trim((string) $request->query('login', ''));

        if ($login === '') {
            return response()->json([
                'state' => 'idle',
                'message' => '',
            ]);
        }

        return response()->json([
            'state' => strlen($login) >= 3 ? 'ready' : 'idle',
            'message' => strlen($login) >= 3
                ? 'Lanjutkan dengan memasukkan password Anda.'
                : '',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        app(ActivityLogger::class)->log(
            action: 'logout',
            actor: $request->user(),
            target: $request->user(),
            description: 'Logout dari aplikasi.',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
