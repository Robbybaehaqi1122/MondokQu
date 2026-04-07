<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        return redirect()
            ->route('profile.edit')
            ->with('error', 'Untuk keamanan akun, Anda wajib mengganti password default sebelum melanjutkan.');
    }
}
