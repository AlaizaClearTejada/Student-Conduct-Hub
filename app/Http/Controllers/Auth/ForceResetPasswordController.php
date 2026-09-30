<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForceResetPasswordController extends Controller
{
    /**
     * Show the forced password reset form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (! $request->user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-reset');
    }

    /**
     * Handle the forced password reset submission.
     *
     * Enforces the 12-character password policy with uppercase, lowercase,
     * numeric, and special character requirements.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ], [
            'password.min' => 'Your password must be at least 12 characters long.',
            'password.mixed' => 'Your password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'Your password must contain at least one number.',
            'password.symbols' => 'Your password must contain at least one special character.',
            'password.uncompromised' => 'This password has appeared in a data breach. Please choose a different password.',
        ]);

        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        AuthAuditLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event_type' => 'forced_password_reset_completed',
            'additional_data' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('dashboard')->with('status', 'Your password has been updated successfully.');
    }
}
