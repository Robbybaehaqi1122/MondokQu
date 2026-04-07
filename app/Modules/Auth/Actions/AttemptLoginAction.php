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

        $this->ensureIsNotRateLimited($request, $login);

        if ($user && ! $user->canAuthenticate()) {
            $this->activityLogger->log(
                action: 'login_failed',
                actor: null,
                target: $user,
                description: 'Percobaan login gagal karena status akun tidak aktif.',
                properties: [
                    'login' => $login,
                    'reason' => $user->status,
                    'actor_name' => 'Guest',
                ],
                ipAddress: $request->ip()
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
                description: 'Percobaan login gagal karena kredensial tidak cocok.',
                properties: [
                    'login' => $login,
                    'reason' => 'invalid_credentials',
                    'actor_name' => 'Guest',
                    'target_name' => $user?->name ? $user->name.' (@'.$user->username.')' : $login,
                ],
                ipAddress: $request->ip()
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
                ],
                ipAddress: $request->ip()
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
