<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('login', 'pages.auth.login')
        ->middleware('throttle:5,1')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    // Forced Password Reset on First Login
    Route::get('password/force-reset', [\App\Http\Controllers\Auth\ForceResetPasswordController::class, 'show'])
        ->name('password.force-reset');
    Route::post('password/force-reset', [\App\Http\Controllers\Auth\ForceResetPasswordController::class, 'update'])
        ->name('password.force-reset.update');

    // Multi-Factor Authentication (MFA) OTP Verification
    Route::get('mfa/verify', [\App\Http\Controllers\Auth\MfaController::class, 'show'])
        ->name('mfa.verify');
    Route::post('mfa/verify', [\App\Http\Controllers\Auth\MfaController::class, 'verify'])
        ->name('mfa.verify.check');
    Route::post('mfa/resend', [\App\Http\Controllers\Auth\MfaController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('mfa.resend');

    Route::post('logout', function () {
        $logout = app(\App\Livewire\Actions\Logout::class);
        $logout();

        return redirect('/');
    })->name('logout');
});
