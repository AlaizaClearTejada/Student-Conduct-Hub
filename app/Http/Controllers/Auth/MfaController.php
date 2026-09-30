<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthAuditLog;
use App\Notifications\MfaOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MfaController extends Controller
{
    /**
     * Show the MFA OTP verification form.
     * Generates and sends an OTP if one is not already active.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasMfaRequired()) {
            return redirect()->route('dashboard');
        }

        if (session('mfa_verified')) {
            return redirect()->route('dashboard');
        }

        // Generate OTP if none exists or if the current one expired
        if ($user->mfa_otp === null || ($user->mfa_otp_expires_at && $user->mfa_otp_expires_at->isPast())) {
            $otp = $user->generateMfaOtp();
            $user->notify(new MfaOtpNotification($otp));
        }

        return view('auth.mfa-verify');
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->mfa_otp_attempts >= 3) {
            AuthAuditLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'event_type' => 'mfa_max_attempts_exceeded',
                'additional_data' => [
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            return back()->withErrors([
                'otp' => 'Maximum verification attempts exceeded. Please request a new code.',
            ]);
        }

        if (! $user->verifyMfaOtp($request->otp)) {
            AuthAuditLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'event_type' => 'mfa_failed',
                'additional_data' => [
                    'attempts' => $user->fresh()->mfa_otp_attempts,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

            return back()->withErrors([
                'otp' => 'Invalid or expired verification code. Please try again.',
            ]);
        }

        // MFA passed — mark session as verified
        session(['mfa_verified' => true]);

        AuthAuditLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event_type' => 'mfa_success',
            'additional_data' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Resend a new OTP code via email.
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        $otp = $user->generateMfaOtp();
        $user->notify(new MfaOtpNotification($otp));

        AuthAuditLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event_type' => 'mfa_otp_resent',
            'additional_data' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return back()->with('status', 'A new verification code has been sent to your email.');
    }
}
