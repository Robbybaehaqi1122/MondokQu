<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttemptLoginAction
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Attempt to authenticate the user using username or email.
     *
     * @throws ValidationException
     */
    public function handle(Request $request, array $credentials, bool $remember = false): void
    {
        $login = (string) ($credentials['login'] ?? '');
        $password = (string) ($credentials['password'] ?? '');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::query()->where($field, $login)->first();
        $userAgent = $request->userAgent();

        $this->ensureIsNotRateLimited($request, $login);

        if ($user && ! $user->canAuthenticate()) {
            $this->activityLogger->log(
                action: 'login_failed',
                actor: null,
                target: $user,
                description: 'Percobaan login gagal karena status akun tidak mengizinkan akses.',
                properties: [
                    'login' => $login,
                    'reason' => match ($user->status) {
                        User::STATUS_INACTIVE => 'account_inactive',
                        User::STATUS_SUSPENDED => 'account_suspended',
                        default => 'account_unavailable',
                    },
                    'status' => $user->status,
                    'actor_name' => 'Guest',
                ],
                ipAddress: $request->ip(),
                userAgent: $userAgent
            );

            throw ValidationException::withMessages([
                'login' => match ($user->status) {
                    User::STATUS_INACTIVE => 'Akun ini sedang nonaktif. Hubungi admin untuk mengaktifkan kembali.',
                    User::STATUS_SUSPENDED => 'Akun ini sedang disuspend. Silakan hubungi admin.',
                    default => trans('auth.failed'),
                },
            ]);
        }

        if (! Auth::attempt([$field => $login, 'password' => $password], $remember)) {
            RateLimiter::hit($this->throttleKey($request, $login));

            $this->activityLogger->log(
                action: 'login_failed',
                actor: null,
                target: $user,
                description: $user
                    ? 'Percobaan login gagal karena password salah.'
                    : 'Percobaan login gagal karena akun tidak ditemukan.',
                properties: [
                    'login' => $login,
                    'reason' => $user ? 'wrong_password' : 'user_not_found',
                    'actor_name' => 'Guest',
                    'target_name' => $user?->name ? $user->name.' (@'.$user->username.')' : $login,
                ],
                ipAddress: $request->ip(),
                userAgent: $userAgent
            );

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, $login));
        $request->session()->regenerate();

        /** @var User|null $authenticatedUser */
        $authenticatedUser = $request->user();

        if ($authenticatedUser) {
            $authenticatedUser->forceFill([
                'last_login_at' => now(),
            ])->save();

            $this->activityLogger->log(
                action: 'login_success',
                actor: $authenticatedUser,
                target: $authenticatedUser,
                description: 'Login berhasil ke aplikasi.',
                properties: [
                    'login' => $login,
                    'remember' => $remember,
                ],
                ipAddress: $request->ip(),
                userAgent: $userAgent
            );
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(Request $request, string $login): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request, $login), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request, $login));

        $user = User::query()
            ->where(filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username', $login)
            ->first();

        $this->activityLogger->log(
            action: 'login_blocked',
            actor: null,
            target: $user,
            description: 'Percobaan login diblokir sementara karena terlalu banyak percobaan.',
            properties: [
                'login' => $login,
                'reason' => 'rate_limited',
                'seconds' => $seconds,
                'actor_name' => 'Guest',
                'target_name' => $user?->name ? $user->name.' (@'.$user->username.')' : $login,
            ],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(Request $request, string $login): string
    {
        return Str::transliterate(Str::lower($login).'|'.$request->ip());
    }
}
