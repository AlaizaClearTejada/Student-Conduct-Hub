<div>
    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-br from-[#590004] via-[#701005] to-[#a50104] px-6 sm:px-8 py-6 sm:py-7">
            <h2 class="text-white font-bold text-xl sm:text-2xl leading-tight">
                {{ $isEditing ? 'Edit Student Record' : 'Register New Student' }}
            </h2>
            <p class="text-red-100 text-sm mt-2 sm:mt-2.5 font-medium">
                {{ $isEditing ? 'Update the student\'s enrollment information.' : 'Fill in all required fields to register a new student.' }}
            </p>
        </div>

        {{-- Form body --}}
        <form wire:submit="save">
            <div class="px-6 sm:px-8 py-6 sm:py-8 bg-gradient-to-br from-white to-gray-50">
                    <div class="space-y-6">

                        {{-- Section Card: Student Identification --}}
                        <div class="bg-white rounded-2xl border border-gray-200/50 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-gradient-to-r from-[#590004] to-[#a50104] px-6 py-4 flex items-center gap-3">
                                <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                </svg>
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Student Identification</h3>
                            </div>
                            <div class="p-6 bg-gradient-to-br from-white to-gray-50/50">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                    Student ID Number <span class="text-[#a50104]">*</span>
                                </label>
                                <input
                                    wire:model.live.debounce.400ms="studentIdInput"
                                    type="text"
                                    placeholder="e.g. 2024-0001"
                                    {{ $isEditing ? 'readonly' : '' }}
                                    class="w-full px-4 py-3 border-2 rounded-xl text-sm font-mono font-semibold focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none transition-all
                                        {{ $isEditing ? 'bg-gray-100 text-gray-500 cursor-not-allowed border-gray-200' : 'bg-white border-gray-300 hover:border-gray-400' }}
                                        {{ $errors->has('studentIdInput') || $studentIdTaken ? 'border-red-400 ring-2 ring-red-100' : '' }}"
                                />
                                @if ($studentIdTaken && ! $isEditing)
                                    <div class="mt-3 p-3 bg-red-50 border border-red-300 rounded-lg flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-xs text-red-700 font-semibold">This Student ID is already registered</p>
                                    </div>
                                @elseif ($studentIdInput && ! $studentIdTaken && ! $isEditing)
                                    <div class="mt-3 p-3 bg-green-50 border border-green-300 rounded-lg flex items-center gap-2">
                                        <svg class="w-4 h-4 shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <p class="text-xs text-green-700 font-semibold">Student ID is available</p>
                                    </div>
                                @endif
                                @error('studentIdInput')
                                    <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                                @if ($isEditing)
                                    <p class="mt-3 text-xs text-gray-500 italic">Student ID is locked to maintain data integrity</p>
                                @endif
                            </div>
                        </div>

                        {{-- Section Card: Personal Information --}}
                        <div class="bg-white rounded-2xl border border-gray-200/50 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-gradient-to-r from-[#590004] to-[#a50104] px-6 py-4 flex items-center gap-3">
                                <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Personal Information</h3>
                            </div>
                            <div class="p-6 bg-gradient-to-br from-white to-gray-50/50 space-y-5">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                            First Name <span class="text-[#a50104]">*</span>
                                        </label>
                                        <input
                                            wire:model="firstName"
                                            type="text"
                                            placeholder="Juan"
                                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none bg-white hover:border-gray-400 transition-all"
                                        />
                                        @error('firstName')
                                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                            Last Name <span class="text-[#a50104]">*</span>
                                        </label>
                                        <input
                                            wire:model="lastName"
                                            type="text"
                                            placeholder="dela Cruz"
                                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none bg-white hover:border-gray-400 transition-all"
                                        />
                                        @error('lastName')
                                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                        Email Address <span class="text-[#a50104]">*</span>
                                    </label>
                                    <input
                                        wire:model="email"
                                        type="email"
                                        placeholder="jdelacruz@csu.edu.ph"
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none bg-white hover:border-gray-400 transition-all"
                                    />
                                    @error('email')
                                        <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section Card: Academic Information --}}
                        <div class="bg-white rounded-2xl border border-gray-200/50 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                            <div class="bg-gradient-to-r from-[#590004] to-[#a50104] px-6 py-4 flex items-center gap-3">
                                <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                </svg>
                                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Academic Information</h3>
                            </div>
                            <div class="p-6 bg-gradient-to-br from-white to-gray-50/50 space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                        College / Department <span class="text-[#a50104]">*</span>
                                    </label>
                                    <select
                                        wire:model.live="college"
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none bg-white hover:border-gray-400 transition-all appearance-none cursor-pointer"
                                    >
                                        <option value="">— Select College —</option>
                                        @foreach ($colleges as $col)
                                            <option value="{{ $col }}">{{ $col }}</option>
                                        @endforeach
                                    </select>
                                    @error('college')
                                        <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                        Program <span class="text-[#a50104]">*</span>
                                    </label>
                                    <select
                                        wire:model="program"
                                        @disabled(! $college || empty($programs))
                                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none bg-white hover:border-gray-400 transition-all appearance-none cursor-pointer disabled:bg-gray-100 disabled:text-gray-500"
                                    >
                                        <option value="">— Select Program —</option>
                                        @foreach ($programs as $availableProgram)
                                            <option value="{{ $availableProgram }}">{{ $availableProgram }}</option>
                                        @endforeach
                                    </select>
                                    @error('program')
                                        <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                            Year Level <span class="text-[#a50104]">*</span>
                                        </label>
                                        <select
                                            wire:model="yearLevel"
                                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none bg-white hover:border-gray-400 transition-all appearance-none cursor-pointer"
                                        >
                                            <option value="">— Select Year —</option>
                                            @foreach ($yearLevels as $year)
                                                <option value="{{ $year }}">{{ $year }}</option>
                                            @endforeach
                                        </select>
                                        @error('yearLevel')
                                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">
                                            Section <span class="text-[#a50104]">*</span>
                                        </label>
                                        <input
                                            wire:model="section"
                                            type="text"
                                            placeholder="e.g. A, 1B, 3C"
                                            maxlength="10"
                                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-sm font-medium focus:ring-2 focus:ring-[#a50104]/20 focus:border-[#a50104] outline-none bg-white hover:border-gray-400 transition-all"
                                        />
                                        @error('section')
                                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                {{-- Info Notice --}}
                        @if (! $isEditing)
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-xl p-5 shadow-sm">
                                <div class="flex gap-4">
                                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-bold text-amber-900 mb-1.5">Password Generation</p>
                                        <p class="text-xs text-amber-800 leading-relaxed">A secure temporary password will be automatically generated. Use the "Send Credentials" feature to deliver login details via email.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 sm:px-8 py-5 bg-gradient-to-r from-white to-gray-50 border-t-2 border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-gray-500 font-medium hidden sm:block"><span class="text-[#a50104] font-bold">*</span> Required fields</p>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <a
                            href="{{ route('staff.students') }}"
                            wire:navigate
                            class="px-6 py-2.5 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-xl transition-all order-2 sm:order-1 w-full sm:w-auto text-center"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-7 py-2.5 bg-gradient-to-r from-[#590004] to-[#a50104] hover:from-[#a50104] hover:to-[#590004] text-white text-sm font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2.5 disabled:opacity-60 disabled:cursor-not-allowed order-1 sm:order-2 w-full sm:w-auto"
                        >
                            <svg wire:loading class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <svg wire:loading.remove class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span wire:loading.remove>{{ $isEditing ? 'Update Record' : 'Save Record' }}</span>
                            <span wire:loading>Processing&hellip;</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
