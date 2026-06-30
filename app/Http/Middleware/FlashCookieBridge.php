<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FlashCookieBridge
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('_saved_notify')) {
            foreach ($request->session()->pull('_saved_notify') as $key => $value) {
                $request->session()->flash($key, $value);
                Log::info("flash.restore.{$key}", ['message' => substr((string) $value, 0, 200)]);
            }
        }

        $response = $next($request);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $session = $request->session();
            $saved = [];
            foreach (['success', 'error'] as $key) {
                if ($session->has($key)) {
                    $saved[$key] = $session->get($key);
                }
            }
            if ($saved) {
                $session->put('_saved_notify', $saved);
                Log::info('flash.preserve', array_map(fn ($v) => substr((string) $v, 0, 200), $saved));
            }
        }

        return $response;
    }
}
