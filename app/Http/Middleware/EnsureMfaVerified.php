<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaVerified
{
    /**
     * Routes that should be accessible even when MFA is pending.
     *
     * @var list<string>
     */
    protected array $except = [
        'mfa.verify',
        'mfa.verify.check',
        'mfa.resend',
        'password.force-reset',
        'password.force-reset.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * If the user's role requires MFA and they haven't verified their OTP
     * for this session, redirect them to the MFA verification page.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Skip if user still needs to reset password first
        if ($user->must_change_password) {
            return $next($request);
        }

        if ($user->hasMfaRequired() && ! session('mfa_verified')) {
            $currentRoute = $request->route()?->getName();

            if (! in_array($currentRoute, $this->except, true)) {
                return redirect()->route('mfa.verify');
            }
        }

        return $next($request);
    }
}
