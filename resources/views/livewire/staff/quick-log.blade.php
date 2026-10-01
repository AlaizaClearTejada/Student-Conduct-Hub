<div>
    <!-- Quick Log Modal -->
    <div 
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="closeModal"></div>
        
        <!-- Modal Content -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div 
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative max-h-[calc(100dvh-2rem)] overflow-y-auto bg-[#f3f3f3] rounded-2xl shadow-2xl max-w-2xl w-full p-4 sm:p-8"
            >
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-[#250001]">Log Minor Infraction</h3>
                        <p class="text-sm text-gray-600 mt-1">Quick entry for uniform violations, missing ID, or tardiness</p>
                    </div>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="submit" class="space-y-6">
                    
                    <!-- Student ID Field with Auto-Focus -->
                    <div>
                        <label for="studentId" class="block text-sm font-bold text-gray-700 mb-2">
                            Student ID <span class="text-[#a50104]">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="studentId" 
                            wire:model.live.debounce.500ms="studentId"
                            x-init="$nextTick(() => $el.focus())"
                            inputmode="numeric"
                            pattern="[0-9]{2}-[0-9]{5}"
                            maxlength="8"
                            placeholder="e.g., 24-00001"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#590004] focus:border-transparent transition-all @error('studentId') border-red-500 @enderror"
                        >
                        @error('studentId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Identity Verification Display -->
                        @if($studentVerified)
                            <div class="mt-2 flex items-center gap-2 text-sm bg-green-50 border border-green-200 rounded-lg p-3">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="flex-1">
                                    <span class="text-green-800 font-medium block">
                                        Verified: <span class="font-bold">{{ $studentInitials }}</span> - {{ $studentProgram }}
                                    </span>
                                    <span class="text-green-700 text-xs mt-0.5 block">
                                        This will be recorded as the student's <strong>{{ $offenseOrdinal }} offense</strong>.
                                    </span>
                                </div>
                            </div>
                        @elseif($studentId && !$studentVerified)
                            <div wire:loading.remove wire:target="updatedStudentId" class="mt-2 flex items-center gap-2 text-sm bg-red-50 border border-red-200 rounded-lg p-3">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-red-800">Student not found or not registered</span>
                            </div>
                        @endif

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="updatedStudentId" class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                            <svg class="animate-spin h-5 w-5 text-[#590004]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Verifying student identity...</span>
                        </div>
                    </div>

                    <!-- Offense Selection -->
                    <div>
                        <label for="offenseId" class="block text-sm font-bold text-gray-700 mb-2">
                            Minor Offense <span class="text-[#a50104]">*</span>
                        </label>
                        <select 
                            id="offenseId" 
                            wire:model="offenseId"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#590004] focus:border-transparent transition-all @error('offenseId') border-red-500 @enderror"
                        >
                            <option value="">Select a minor offense...</option>
                            @foreach($minorOffenses as $offense)
                                <option value="{{ $offense->id }}">
                                    {{ $offense->code }} - {{ $offense->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('offenseId')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Brief Description -->
                    <div>
                        <label for="description" class="block text-sm font-bold text-gray-700 mb-2">
                            Brief Description <span class="text-[#a50104]">*</span>
                        </label>
                        <textarea 
                            id="description" 
                            wire:model="description"
                            rows="4"
                            placeholder="Briefly describe the incident (e.g., 'No ID displayed at gate entry, 8:30 AM')"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#590004] focus:border-transparent transition-all resize-none @error('description') border-red-500 @enderror"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">Minimum 10 characters</p>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <button 
                            type="button" 
                            wire:click="closeModal"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-8 py-3 bg-[#590004] text-white rounded-lg font-bold hover:bg-[#250001] transition-colors shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="submit">Submit Log</span>
                            <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Submitting...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
