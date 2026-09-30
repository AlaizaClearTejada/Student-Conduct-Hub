<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordResetOnFirstLogin
{
    /**
     * Routes that should be accessible even when forced reset is pending.
     *
     * @var list<string>
     */
    protected array $except = [
        'password.force-reset',
        'password.force-reset.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * Redirects users with must_change_password=true to the forced reset page,
     * blocking all dashboard access until they create a unique password meeting
     * the 12-character policy requirements.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            $currentRoute = $request->route()?->getName();

            if (! in_array($currentRoute, $this->except, true)) {
                return redirect()->route('password.force-reset');
            }
        }

        return $next($request);
    }
}
