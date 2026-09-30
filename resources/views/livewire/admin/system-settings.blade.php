<div>
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-mahogany">System Configuration</h1>
        <p class="text-gray-600">Configure system settings, security options, and integrations</p>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button wire:click="setTab('general')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'general' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        General
                    </div>
                </button>
                <button wire:click="setTab('security')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'security' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Security
                    </div>
                </button>
                <button wire:click="setTab('email')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'email' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Email
                    </div>
                </button>
                <button wire:click="setTab('notifications')"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 'notifications' ? 'border-inferno text-inferno' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notifications
                    </div>
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            {{-- General Settings --}}
            @if($activeTab === 'general')
                <form wire:submit="saveGeneral" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="appName" class="block text-sm font-medium text-gray-700 mb-2">Application Name</label>
                            <input type="text" id="appName" wire:model="appName"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            @error('appName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="academicYear" class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                            <input type="text" id="academicYear" wire:model="academicYear" placeholder="2025-2026"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            @error('academicYear') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                            <select id="timezone" wire:model="timezone"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                                @foreach($timezones as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="dateFormat" class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                            <select id="dateFormat" wire:model="dateFormat"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                                @foreach($dateFormats as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                                wire:target="saveGeneral"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-inferno text-white rounded-lg hover:bg-black-cherry transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="saveGeneral" class="animate-spin -ml-1 mr-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveGeneral">Save General Settings</span>
                            <span wire:loading wire:target="saveGeneral">Saving...</span>
                        </button>
                    </div>
                </form>
            @endif

            {{-- Security Settings --}}
            @if($activeTab === 'security')
                <form wire:submit="saveSecurity" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="sessionTimeout" class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                            <input type="number" id="sessionTimeout" wire:model="sessionTimeout" min="5" max="480"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            <p class="text-xs text-gray-500 mt-1">User will be logged out after this period of inactivity</p>
                            @error('sessionTimeout') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="maxLoginAttempts" class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
                            <input type="number" id="maxLoginAttempts" wire:model="maxLoginAttempts" min="3" max="10"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            <p class="text-xs text-gray-500 mt-1">Account locked after this many failed attempts</p>
                            @error('maxLoginAttempts') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="lockoutDuration" class="block text-sm font-medium text-gray-700 mb-2">Lockout Duration (minutes)</label>
                            <input type="number" id="lockoutDuration" wire:model="lockoutDuration" min="5" max="60"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            <p class="text-xs text-gray-500 mt-1">How long the account stays locked</p>
                            @error('lockoutDuration') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="passwordMinLength" class="block text-sm font-medium text-gray-700 mb-2">Minimum Password Length</label>
                            <input type="number" id="passwordMinLength" wire:model="passwordMinLength" min="6" max="32"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            @error('passwordMinLength') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">Password Requirements</h4>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="requireUppercase"
                                       class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                                <span class="text-sm text-gray-700">Require at least one uppercase letter</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="requireNumber"
                                       class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                                <span class="text-sm text-gray-700">Require at least one number</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="requireSpecialChar"
                                       class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                                <span class="text-sm text-gray-700">Require at least one special character (!@#$%^&*)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                                wire:target="saveSecurity"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-inferno text-white rounded-lg hover:bg-black-cherry transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="saveSecurity" class="animate-spin -ml-1 mr-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveSecurity">Save Security Settings</span>
                            <span wire:loading wire:target="saveSecurity">Saving...</span>
                        </button>
                    </div>
                </form>
            @endif

            {{-- Email Settings --}}
            @if($activeTab === 'email')
                <form wire:submit="saveEmail" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fromAddress" class="block text-sm font-medium text-gray-700 mb-2">From Email Address</label>
                            <input type="email" id="fromAddress" wire:model="fromAddress"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            @error('fromAddress') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="fromName" class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                            <input type="text" id="fromName" wire:model="fromName"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-inferno focus:border-inferno">
                            @error('fromName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">Email Notifications</h4>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="sendWelcomeEmail"
                                       class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                                <span class="text-sm text-gray-700">Send welcome email to new students with credentials</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="sendIncidentNotifications"
                                       class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                                <span class="text-sm text-gray-700">Send email notifications for incident updates</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800">SMTP Configuration</p>
                                <p class="text-xs text-amber-700 mt-1">SMTP settings are configured via environment variables (.env file) for security. Contact your system administrator to modify mail server settings.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                                wire:target="saveEmail"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-inferno text-white rounded-lg hover:bg-black-cherry transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="saveEmail" class="animate-spin -ml-1 mr-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveEmail">Save Email Settings</span>
                            <span wire:loading wire:target="saveEmail">Saving...</span>
                        </button>
                    </div>
                </form>
            @endif

            {{-- Notification Settings --}}
            @if($activeTab === 'notifications')
                <form wire:submit="saveNotifications" class="space-y-6">
                    <div class="space-y-4">
                        <h4 class="text-sm font-semibold text-gray-700">In-App Notifications</h4>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                            <div>
                                <span class="text-sm font-medium text-gray-900">New Incident Reports</span>
                                <p class="text-xs text-gray-500">Notify administrators when new incident reports are submitted</p>
                            </div>
                            <input type="checkbox" wire:model="notifyOnNewIncident"
                                   class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Case Status Updates</span>
                                <p class="text-xs text-gray-500">Notify relevant parties when case status changes</p>
                            </div>
                            <input type="checkbox" wire:model="notifyOnCaseUpdate"
                                   class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Login Failure Alerts</span>
                                <p class="text-xs text-gray-500">Alert administrators about suspicious login attempts</p>
                            </div>
                            <input type="checkbox" wire:model="notifyAdminOnLoginFailure"
                                   class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                        </label>

                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Daily Summary Email</span>
                                <p class="text-xs text-gray-500">Receive a daily digest of system activity</p>
                            </div>
                            <input type="checkbox" wire:model="dailySummaryEmail"
                                   class="w-5 h-5 text-inferno border-gray-300 rounded focus:ring-inferno">
                        </label>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit"
                                wire:target="saveNotifications"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-inferno text-white rounded-lg hover:bg-black-cherry transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg wire:loading wire:target="saveNotifications" class="animate-spin -ml-1 mr-1 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveNotifications">Save Notification Settings</span>
                            <span wire:loading wire:target="saveNotifications">Saving...</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
