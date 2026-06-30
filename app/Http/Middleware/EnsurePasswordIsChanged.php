<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->password_change_required) {
            return $next($request);
        }

        if ($request->routeIs([
            'profile.edit',
            'profile.update',
            'password.update',
            'logout',
        ])) {
            return $next($request);
        }

        Log::warning('middleware.password_change_required', [
            'user_id' => $user->id ?? '?',
            'target_route' => 'profile.edit',
        ]);

        $request->session()->reflash();

        return redirect()
            ->route('profile.edit')
            ->with('error', 'Untuk keamanan akun, Anda wajib mengganti password default sebelum melanjutkan.');
    }
}
