<?php

namespace App\Livewire\Forms;

use App\Models\AuthAuditLog;
use App\Models\Setting;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string')]
    public string $login = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     * Supports login via email or username.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentialField = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $credentialField => $this->login,
            'password' => $this->password,
        ];

        if (! Auth::attempt($credentials, $this->remember)) {
            $lockoutDuration = Setting::get('security', 'lockout_duration', 15) * 60;
            RateLimiter::hit($this->throttleKey(), $lockoutDuration);

            // Log failed authentication attempt
            $this->logAuthenticationAttempt('login_failed');

            throw ValidationException::withMessages([
                'form.login' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Log successful authentication
        $this->logAuthenticationAttempt('login_success', Auth::id());
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        $maxAttempts = Setting::get('security', 'max_login_attempts', 5);

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        event(new Lockout(request()));

        // Log lockout event
        $this->logAuthenticationAttempt('lockout');

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->login).'|'.request()->ip());
    }

    /**
     * Log authentication attempt for audit trail.
     */
    protected function logAuthenticationAttempt(string $eventType, ?int $userId = null): void
    {
        AuthAuditLog::create([
            'user_id' => $userId,
            'email' => $this->login,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event_type' => $eventType,
            'additional_data' => [
                'login_field' => filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username',
                'remember' => $this->remember,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
