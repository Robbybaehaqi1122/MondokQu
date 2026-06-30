<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckImpersonation
{
    /**
     * Ensure an active impersonation session still belongs to a Superadmin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $impersonatorId = $request->session()->get('impersonation.impersonator_id');

        if (! $impersonatorId) {
            return $next($request);
        }

        $impersonator = User::query()->find($impersonatorId);

        if ($impersonator?->isSuperAdmin()) {
            return $next($request);
        }

        \Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
            \Sentry\Breadcrumb::LEVEL_WARNING,
            \Sentry\Breadcrumb::TYPE_NAVIGATION,
            'middleware',
            'CheckImpersonation: invalidating session for user ' . ($request->user()?->id ?? '?')
        ));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('error', 'Sesi impersonation tidak valid karena akses Superadmin telah dicabut. Silakan login ulang.');
    }
}
