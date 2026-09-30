<x-guest-layout>
    <div>
        <!-- Header -->
        <div class="mb-8 text-center">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight" style="color: #250001;">
                Security Verification
            </h2>
            <p class="text-sm mt-2 text-gray-600">
                A 6-digit verification code has been sent to your email address.
                Please enter it below to continue.
            </p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-green-800 font-medium">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('mfa.verify.check') }}" id="mfaForm" x-data="otpForm()">
            @csrf

            <!-- OTP Input -->
            <div class="mb-6">
                <label for="otp" class="block text-sm font-semibold text-gray-700 mb-2">Verification Code</label>
                <div class="flex justify-center gap-2">
                    @php $oldOtp = old('otp', ''); @endphp
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            maxlength="1"
                            inputmode="numeric"
                            pattern="[0-9]"
                            class="w-12 h-14 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-[#590004] focus:ring-2 focus:ring-[#590004]/20 transition-all"
                            id="otp-{{ $i }}"
                            x-model="digits[{{ $i }}]"
                            @input="handleInput({{ $i }}, $event)"
                            @keydown.backspace="handleBackspace({{ $i }}, $event)"
                            @paste.prevent="handlePaste($event)"
                            autocomplete="one-time-code"
                        />
                    @endfor
                </div>
                <input type="hidden" name="otp" :value="digits.join('')" />

                @error('otp')
                    <p class="mt-2 text-sm text-center" style="color: #a50104;">
                        <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Timer & Code Info -->
            <div class="mb-6 text-center" x-data="{ timeLeft: 600, expired: false }" x-init="
                let timer = setInterval(() => {
                    timeLeft--;
                    if (timeLeft <= 0) {
                        expired = true;
                        clearInterval(timer);
                    }
                }, 1000);
            ">
                <p class="text-xs text-gray-500" x-show="!expired">
                    Code expires in
                    <span class="font-mono font-semibold" :class="timeLeft <= 60 ? 'text-red-600' : 'text-gray-700'" x-text="Math.floor(timeLeft / 60) + ':' + String(timeLeft % 60).padStart(2, '0')"></span>
                </p>
                <p class="text-xs text-red-600 font-medium" x-show="expired">
                    Your code has expired. Please request a new one.
                </p>
            </div>

            <!-- Verify Button -->
            <button
                type="submit"
                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                style="background-color: #a50104; box-shadow: 0 4px 14px 0 rgba(165, 1, 4, 0.39);"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Verify Code</span>
            </button>
        </form>

        <!-- Resend OTP -->
        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('mfa.resend') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm font-semibold hover:underline transition-all underline-offset-4" style="color: #590004;">
                    Didn't receive a code? Resend
                </button>
            </form>
        </div>

        <!-- Logout link -->
        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    Sign out and use a different account
                </button>
            </form>
        </div>
    </div>

    <!-- Require Alpine.js (Livewire injects it normally, but just in case this page loads standalone) -->
    @livewireScripts
    <script>
        function otpForm() {
            const oldOtp = "{{ old('otp', '') }}";
            let initialDigits = ['', '', '', '', '', ''];
            
            if (oldOtp && oldOtp.length === 6) {
                initialDigits = oldOtp.split('');
            }
            
            return {
                digits: initialDigits,
                handleInput(index, event) {
                    const value = event.target.value.replace(/\D/g, '');
                    this.digits[index] = value.slice(-1);
                    event.target.value = this.digits[index];
                    
                    if (value && index < 5) {
                        const nextEl = document.getElementById('otp-' + (index + 1));
                        if(nextEl) {
                            nextEl.focus();
                            nextEl.select();
                        }
                    }
                },
                handleBackspace(index, event) {
                    if (!this.digits[index] && index > 0) {
                        const prevEl = document.getElementById('otp-' + (index - 1));
                        if(prevEl) {
                            prevEl.focus();
                            prevEl.select();
                        }
                    }
                },
                handlePaste(event) {
                    const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                    for (let i = 0; i < 6; i++) {
                        this.digits[i] = pasted[i] || '';
                    }
                    const lastFilledIndex = Math.min(pasted.length, 5);
                    const el = document.getElementById('otp-' + lastFilledIndex);
                    if(el) el.focus();
                }
            }
        }
    </script>
</x-guest-layout>
