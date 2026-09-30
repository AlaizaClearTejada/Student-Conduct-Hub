<x-guest-layout>
    <div>
        <!-- Header -->
        <div class="mb-8 text-center">
            <div class="mx-auto w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight" style="color: #250001;">
                Password Update Required
            </h2>
            <p class="text-sm mt-2 text-gray-600">
                For your security, you must create a new password before accessing the system.
            </p>
        </div>

        <!-- Password Policy Indicators -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200" x-data="passwordValidator()">
            <p class="text-xs font-semibold text-gray-700 mb-3 uppercase tracking-wide">Password Requirements</p>
            <div class="grid grid-cols-2 gap-2">
                <div class="flex items-center gap-2 text-xs" :class="checks.length ? 'text-green-600' : 'text-gray-400'">
                    <svg class="w-4 h-4" :class="checks.length ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                        <path x-show="checks.length" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        <path x-show="!checks.length" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>12+ characters</span>
                </div>
                <div class="flex items-center gap-2 text-xs" :class="checks.uppercase ? 'text-green-600' : 'text-gray-400'">
                    <svg class="w-4 h-4" :class="checks.uppercase ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                        <path x-show="checks.uppercase" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        <path x-show="!checks.uppercase" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Uppercase letter</span>
                </div>
                <div class="flex items-center gap-2 text-xs" :class="checks.lowercase ? 'text-green-600' : 'text-gray-400'">
                    <svg class="w-4 h-4" :class="checks.lowercase ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                        <path x-show="checks.lowercase" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        <path x-show="!checks.lowercase" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Lowercase letter</span>
                </div>
                <div class="flex items-center gap-2 text-xs" :class="checks.number ? 'text-green-600' : 'text-gray-400'">
                    <svg class="w-4 h-4" :class="checks.number ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                        <path x-show="checks.number" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        <path x-show="!checks.number" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Number</span>
                </div>
                <div class="flex items-center gap-2 text-xs" :class="checks.symbol ? 'text-green-600' : 'text-gray-400'">
                    <svg class="w-4 h-4" :class="checks.symbol ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                        <path x-show="checks.symbol" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        <path x-show="!checks.symbol" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Special character</span>
                </div>
                <div class="flex items-center gap-2 text-xs" :class="checks.match ? 'text-green-600' : 'text-gray-400'">
                    <svg class="w-4 h-4" :class="checks.match ? 'text-green-500' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                        <path x-show="checks.match" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        <path x-show="!checks.match" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                    </svg>
                    <span>Passwords match</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('password.force-reset.update') }}" id="forceResetForm">
            @csrf

            <!-- New Password -->
            <div class="floating-label-group" x-data="{ showPassword: false }">
                <div class="relative">
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder=" "
                        class="floating-input peer"
                        aria-describedby="password-error"
                        @input="$dispatch('password-changed', { value: $event.target.value })"
                    />
                    <label for="password" class="floating-label">
                        New Password
                    </label>
                    <button
                        type="button"
                        class="password-toggle"
                        @click.prevent="showPassword = !showPassword"
                        tabindex="-1"
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
                @error('password')
                    <p class="mt-1 text-sm" style="color: #a50104;" id="password-error">
                        <svg class="inline w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="floating-label-group">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder=" "
                    class="floating-input peer"
                    @input="$dispatch('confirm-changed', { value: $event.target.value })"
                />
                <label for="password_confirmation" class="floating-label">
                    Confirm New Password
                </label>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                id="submitBtn"
                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-offset-2 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                style="background-color: #a50104; box-shadow: 0 4px 14px 0 rgba(165, 1, 4, 0.39);"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Set New Password</span>
            </button>
        </form>
    </div>

    <script>
        function passwordValidator() {
            return {
                checks: {
                    length: false,
                    uppercase: false,
                    lowercase: false,
                    number: false,
                    symbol: false,
                    match: false,
                },
                password: '',
                confirm: '',
                init() {
                    this.$el.addEventListener('password-changed', (e) => {
                        this.password = e.detail.value;
                        this.validate();
                    });
                    this.$el.addEventListener('confirm-changed', (e) => {
                        this.confirm = e.detail.value;
                        this.validate();
                    });
                },
                validate() {
                    this.checks.length = this.password.length >= 12;
                    this.checks.uppercase = /[A-Z]/.test(this.password);
                    this.checks.lowercase = /[a-z]/.test(this.password);
                    this.checks.number = /[0-9]/.test(this.password);
                    this.checks.symbol = /[^A-Za-z0-9]/.test(this.password);
                    this.checks.match = this.password.length > 0 && this.password === this.confirm;
                }
            }
        }
    </script>
</x-guest-layout>
