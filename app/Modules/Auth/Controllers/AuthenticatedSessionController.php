<?php

namespace App\Modules\Auth\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\AttemptLoginAction;
use App\Modules\Auth\Actions\DetermineDashboardRouteAction;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                'exists' => false,
            ]);
        }

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return response()->json([
            'exists' => User::query()->where($field, $login)->exists(),
        ]);
    }

    /**
     * Check whether the provided password matches the selected identity.
     */
    public function checkPassword(Request $request): JsonResponse
    {
        $login = trim((string) $request->input('login', ''));
        $password = (string) $request->input('password', '');

        if ($login === '' || $password === '') {
            return response()->json([
                'valid' => false,
                'message' => '',
            ]);
        }

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::query()->where($field, $login)->first();

        if (! $user) {
            return response()->json([
                'valid' => false,
                'message' => 'User belum terdaftar.',
            ]);
        }

        $valid = Hash::check($password, $user->password);

        return response()->json([
            'valid' => $valid,
            'message' => $valid ? 'Password benar.' : 'Password salah.',
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
