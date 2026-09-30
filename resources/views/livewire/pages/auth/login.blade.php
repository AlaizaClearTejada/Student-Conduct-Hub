<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();

        // Forced password reset takes highest priority
        if ($user->must_change_password) {
            $this->redirect(route('password.force-reset', absolute: false), navigate: true);
            return;
        }

        // MFA challenge for privileged roles
        if ($user->hasMfaRequired()) {
            $this->redirect(route('mfa.verify', absolute: false), navigate: true);
            return;
        }

        // Role-based redirect (ignore intended URL for dashboards)
        if ($user->hasRole('administrator')) {
            $this->redirect(route('admin.dashboard', absolute: false), navigate: true);
            return;
        }

        if ($user->hasRole('staff')) {
            $this->redirect(route('staff.dashboard', absolute: false), navigate: true);
            return;
        }

        if ($user->hasRole('student')) {
            $this->redirect(route('student.dashboard', absolute: false), navigate: true);
            return;
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <!-- Logo and Header -->
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-bold tracking-tight" style="color: #250001;">
            Sign In to Your Account
        </h2>
        <p class="text-sm mt-2" style="color: #590004;">
            Enter your credentials to access the system
        </p>
    </div>

    <form wire:submit="login" id="loginForm" novalidate>
        <!-- Email or Username with Floating Label -->
        <div class="floating-label-group">
            <input 
                wire:model="form.login" 
                id="login" 
                type="text" 
                name="login" 
                required 
                autofocus 
                autocomplete="username"
                placeholder=" "
                class="floating-input peer"
                aria-describedby="login-error"
            />
            <label for="login" class="floating-label">
                Email or Username
            </label>
            @error('form.login')
                <p class="mt-1 text-sm" style="color: #a50104;" id="login-error">
                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password with Floating Label and Visibility Toggle -->
        <div class="floating-label-group">
            <div class="relative" x-data="{ showPassword: false }">
                <input 
                    wire:model="form.password" 
                    id="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder=" "
                    class="floating-input peer"
                    aria-describedby="password-error"
                    minlength="8"
                />
                <label for="password" class="floating-label">
                    Password
                </label>
                <button 
                    type="button" 
                    class="password-toggle" 
                    @click.prevent="showPassword = !showPassword"
                    tabindex="-1"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @error('form.password')
                <p class="mt-1 text-sm" style="color: #a50104;" id="password-error">
                    <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember" class="inline-flex items-center cursor-pointer group">
                <input 
                    wire:model="form.remember" 
                    id="remember" 
                    type="checkbox" 
                    class="rounded border-gray-300 text-[#a50104] shadow-sm focus:ring-[#590004] focus:ring-2 transition"
                    name="remember"
                >
                <span class="ms-2 text-sm font-medium group-hover:opacity-80 transition" style="color: #250001;">
                    Remember me
                </span>
            </label>

            @if (Route::has('password.request'))
                <a 
                    class="text-sm font-semibold hover:underline transition-all underline-offset-4" 
                    style="color: #590004;"
                    href="{{ route('password.request') }}" 
                    wire:navigate
                >
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Secure Login Button -->
        <button 
            type="submit"
            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            style="background-color: #a50104; box-shadow: 0 4px 14px 0 rgba(165, 1, 4, 0.39);"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <span>Secure Log In</span>
        </button>

        <!-- Divider -->
        @if (Route::has('register'))
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">New to the system?</span>
                </div>
            </div>

            <!-- Create Account Link -->
            <div class="text-center">
                <a 
                    href="{{ route('register') }}" 
                    class="inline-flex items-center gap-2 text-sm font-semibold hover:underline transition-all underline-offset-4"
                    style="color: #590004;"
                    wire:navigate
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>Create your account</span>
                </a>
            </div>
        @endif
    </form>
</div>

@script
<script>
    // Enhanced Client-side Validation
    document.getElementById('loginForm')?.addEventListener('submit', function(e) {
        const loginInput = document.getElementById('login');
        const passwordInput = document.getElementById('password');
        let isValid = true;

        // Login field validation (email or username)
        if (loginInput) {
            if (loginInput.value.trim().length < 2) {
                loginInput.classList.add('error');
                loginInput.setCustomValidity('Please enter your email or username');
                isValid = false;
            } else {
                loginInput.classList.remove('error');
                loginInput.setCustomValidity('');
            }
        }

        // Password validation with visual feedback
        if (passwordInput) {
            if (passwordInput.value.length < 8) {
                passwordInput.classList.add('error');
                passwordInput.setCustomValidity('Password must be at least 8 characters long');
                isValid = false;
            } else {
                passwordInput.classList.remove('error');
                passwordInput.setCustomValidity('');
            }
        }

        if (!isValid) {
            e.preventDefault();
            this.reportValidity();
        }
    });

    // Clear error state on input
    document.getElementById('login')?.addEventListener('input', function() {
        this.classList.remove('error');
        this.setCustomValidity('');
    });

    document.getElementById('password')?.addEventListener('input', function() {
        this.classList.remove('error');
        this.setCustomValidity('');
    });

</script>
@endscript
