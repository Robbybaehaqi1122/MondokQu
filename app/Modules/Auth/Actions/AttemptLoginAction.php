<?php

namespace App\Modules\Auth\Actions;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttemptLoginAction
{
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

        $this->ensureIsNotRateLimited($request, $login);

        if (! Auth::attempt([$field => $login, 'password' => $password], $remember)) {
            RateLimiter::hit($this->throttleKey($request, $login));

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, $login));
        $request->session()->regenerate();
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
