<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
        'token',
        'secret',
        'api_token',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $start = hrtime(true);

        $response = $next($request);

        if (! $this->shouldLog($request)) {
            return $response;
        }

        try {
            $durationMs = (int) ((hrtime(true) - $start) / 1_000_000);

            $user = $request->user();

            AuditLog::create([
                'tenant_id' => $user instanceof User ? $user->tenant_id : null,
                'user_id' => $user?->id,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->sanitizeRequestData($request),
                'response_status' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('AuditMiddleware gagal mencatat request: ' . $e->getMessage());
        }

        return $response;
    }

    protected function shouldLog(Request $request): bool
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return true;
    }

    protected function sanitizeRequestData(Request $request): array
    {
        $data = $request->except($this->sensitiveFields);

        return $data;
    }
}
