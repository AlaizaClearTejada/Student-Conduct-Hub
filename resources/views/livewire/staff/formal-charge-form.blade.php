<div class="space-y-6">

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 1: Student Information                      --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md border-t-4 border-[#a50104] overflow-hidden">

        <div class="px-8 py-5 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#f3f3f3] text-[#a50104] flex items-center justify-center font-bold text-sm">
                1
            </div>
            <div>
                <h2 class="text-xl font-bold text-[#250001]">Student Information</h2>
                <p class="text-sm text-gray-500">Verify the identity of the student involved in the incident.</p>
            </div>
        </div>

        <div class="p-4 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Student ID (auto-fetch trigger) --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="studentIdInput" class="block text-sm font-bold text-[#250001] mb-1">
                        Student ID No. <span class="text-[#a50104]">*</span>
                    </label>
                    <div class="relative">
                        <input
                            wire:model.live.debounce.500ms="studentIdInput"
                            type="text"
                            id="studentIdInput"
                            placeholder="e.g., 2023-1234"
                            autocomplete="off"
                            class="w-full px-4 py-3 rounded-lg border @error('studentDbId') border-[#a50104] bg-red-50 @else border-gray-300 bg-[#f3f3f3] @enderror focus:ring-2 focus:ring-[#590004] focus:border-[#590004] transition-colors font-mono text-gray-900"
                        >
                        <div wire:loading wire:target="updatedStudentIdInput" class="absolute right-3 top-3.5">
                            <svg class="animate-spin h-5 w-5 text-[#590004]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Verification badge --}}
                    @if ($studentVerified)
                        <div wire:loading.remove wire:target="updatedStudentIdInput" class="mt-2 flex items-center gap-2 text-sm bg-green-50 border border-green-200 rounded-lg p-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <span class="text-green-800 font-medium block">Identity Verified: <strong>{{ $studentName }}</strong></span>
                                <span class="text-green-700 text-xs mt-0.5 block">
                                    This will be recorded as the student's <strong>{{ \Illuminate\Support\Number::ordinal($offenseCount) }} offense</strong>.
                                </span>
                            </div>
                        </div>
                    @elseif (strlen($studentIdInput) > 0 && ! $studentVerified)
                        <div wire:loading.remove wire:target="updatedStudentIdInput" class="mt-2 flex items-center gap-2 text-sm bg-red-50 border border-red-200 rounded-lg p-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-red-700">Student not found. Confirm the ID is correct and registered.</span>
                        </div>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Entering a valid ID will auto-populate the remaining fields.</p>
                    @endif

                    @error('studentDbId')
                        <p class="mt-1 text-xs font-bold text-[#a50104]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- College --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="college" class="block text-sm font-bold text-[#250001] mb-1">
                        College or Department <span class="text-[#a50104]">*</span>
                    </label>
                    <select
                        wire:model="college"
                        id="college"
                        class="w-full px-4 py-3 rounded-lg border @error('college') border-[#a50104] bg-red-50 @else border-gray-300 bg-white @enderror focus:ring-2 focus:ring-[#590004] focus:border-[#590004] transition-colors text-gray-900 disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:cursor-not-allowed"
                        @if($studentVerified) disabled @endif
                    >
                        <option value="">Select College...</option>
                        @foreach ($colleges as $col)
                            <option value="{{ $col }}">{{ $col }}</option>
                        @endforeach
                    </select>
                    @error('college')
                        <p class="mt-1 text-xs font-bold text-[#a50104]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Year Level --}}
                <div>
                    <label for="yearLevel" class="block text-sm font-bold text-[#250001] mb-1">
                        Year Level <span class="text-[#a50104]">*</span>
                    </label>
                    <select
                        wire:model="yearLevel"
                        id="yearLevel"
                        class="w-full px-4 py-3 rounded-lg border @error('yearLevel') border-[#a50104] bg-red-50 @else border-gray-300 bg-white @enderror focus:ring-2 focus:ring-[#590004] focus:border-[#590004] transition-colors text-gray-900 disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:cursor-not-allowed"
                        @if($studentVerified) disabled @endif
                    >
                        <option value="">Select Year...</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                    @error('yearLevel')
                        <p class="mt-1 text-xs font-bold text-[#a50104]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Section --}}
                <div>
                    <label for="section" class="block text-sm font-bold text-[#250001] mb-1">
                        Section <span class="text-[#a50104]">*</span>
                    </label>
                    <input
                        wire:model="section"
                        type="text"
                        id="section"
                        placeholder="e.g., A, B, or 1A"
                        class="w-full px-4 py-3 rounded-lg border @error('section') border-[#a50104] bg-red-50 @else border-gray-300 bg-white @enderror focus:ring-2 focus:ring-[#590004] focus:border-[#590004] transition-colors text-gray-900 disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:cursor-not-allowed"
                        @if($studentVerified) disabled @endif
                    >
                    @error('section')
                        <p class="mt-1 text-xs font-bold text-[#a50104]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-span-1 md:col-span-2">
                    <label for="email" class="block text-sm font-bold text-[#250001] mb-1">
                        Email Address <span class="text-[#a50104]">*</span>
                    </label>
                    <input
                        wire:model="email"
                        type="email"
                        id="email"
                        placeholder="student@csu.edu.ph"
                        class="w-full px-4 py-3 rounded-lg border @error('email') border-[#a50104] bg-red-50 @else border-gray-300 bg-white @enderror focus:ring-2 focus:ring-[#590004] focus:border-[#590004] transition-colors text-gray-900 disabled:bg-gray-100 disabled:text-gray-500 disabled:border-gray-200 disabled:cursor-not-allowed"
                        @if($studentVerified) disabled @endif
                    >
                    <div class="flex items-start gap-2 mt-2">
                        <svg class="w-4 h-4 text-[#a50104] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-xs text-[#590004] font-medium">Crucial: This email will be used to send the official Notice of Hearing and final appeal instructions to the student.</p>
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs font-bold text-[#a50104]">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 2: Incident Details & Evidence              --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">

        <div class="px-8 py-5 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#f3f3f3] text-[#250001] flex items-center justify-center font-bold text-sm">
                2
            </div>
            <div>
                <h2 class="text-xl font-bold text-[#250001]">Incident Details & Evidence</h2>
                <p class="text-sm text-gray-500">Document the violation and attach supporting proof.</p>
            </div>
        </div>

        <div class="p-4 sm:p-8 space-y-6">

            {{-- Offense --}}
            <div>
                <label for="offenseId" class="block text-sm font-bold text-[#250001] mb-1">
                    Select Major Offense <span class="text-[#a50104]">*</span>
                </label>
                <select
                    wire:model="offenseId"
                    id="offenseId"
                    class="w-full px-4 py-3 rounded-lg border @error('offenseId') border-[#a50104] bg-red-50 @else border-gray-300 bg-white @enderror focus:ring-2 focus:ring-[#590004] focus:border-[#590004] transition-colors text-gray-900"
                >
                    <option value="">Choose the specific violation from the CSU Manual...</option>
                    @foreach ($majorOffenses as $offense)
                        <option value="{{ $offense->id }}">
                            {{ $offense->code }} — {{ Str::limit($offense->title, 80) }}
                        </option>
                    @endforeach
                </select>
                @error('offenseId')
                    <p class="mt-1 text-xs font-bold text-[#a50104]">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="incidentDescription" class="block text-sm font-bold text-[#250001] mb-1">
                    Detailed Description <span class="text-[#a50104]">*</span>
                </label>
                <textarea
                    wire:model="incidentDescription"
                    id="incidentDescription"
                    rows="5"
                    placeholder="Provide a factual, objective account of the incident. Include date, time, location, and specific actions observed."
                    class="w-full px-4 py-3 rounded-lg border @error('incidentDescription') border-[#a50104] bg-red-50 @else border-gray-300 bg-white @enderror focus:ring-2 focus:ring-[#590004] focus:border-[#590004] transition-colors text-gray-900 resize-none"
                ></textarea>
                @error('incidentDescription')
                    <p class="mt-1 text-xs font-bold text-[#a50104]">{{ $message }}</p>
                @enderror
            </div>

            {{-- Evidence Upload --}}
            <div>
                <label class="block text-sm font-bold text-[#250001] mb-2">
                    Attach Evidence (Mandatory) <span class="text-[#a50104]">*</span>
                </label>

                <div class="relative mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-xl transition-colors @error('evidenceFile') border-[#a50104] bg-red-50 @else border-gray-300 hover:border-[#590004] hover:bg-gray-50 @enderror">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="evidenceFile" class="relative cursor-pointer bg-white rounded-md font-medium text-[#a50104] hover:text-[#590004] focus-within:outline-none">
                                <span>Upload a file</span>
                                <input wire:model="evidenceFile" id="evidenceFile" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf,.mp4">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, PDF, or MP4 — up to 10 MB</p>
                    </div>

                    {{-- Upload progress overlay --}}
                    <div wire:loading wire:target="evidenceFile" class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center rounded-xl">
                        <div class="flex items-center gap-3 text-[#590004] font-bold text-sm">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading evidence securely...
                        </div>
                    </div>
                </div>

                {{-- File attached confirmation --}}
                @if ($evidenceFile)
                    <div class="mt-3 px-4 py-2 bg-green-50 text-green-800 border border-green-200 rounded-lg text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        File attached: <strong>{{ $evidenceFile->getClientOriginalName() }}</strong>
                    </div>
                @endif

                @error('evidenceFile')
                    <p class="mt-1 text-xs font-bold text-[#a50104] block">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- SECTION 3: Certification & Submit                   --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md border border-gray-200 p-4 sm:p-8 mb-8">

        <div class="flex items-start gap-4 mb-6">
            <div class="flex items-center h-5 mt-1">
                <input
                    wire:model="certification"
                    id="certification"
                    type="checkbox"
                    class="w-5 h-5 text-[#a50104] bg-gray-100 border-gray-300 rounded focus:ring-[#590004] focus:ring-2 cursor-pointer"
                >
            </div>
            <div class="text-sm">
                <label for="certification" class="font-bold text-[#250001] cursor-pointer">
                    Official Certification <span class="text-[#a50104]">*</span>
                </label>
                <p class="text-gray-600 mt-1">
                    By submitting this form, I certify that the information provided is true and accurate to the best of my knowledge.
                    I understand that this initiates a formal disciplinary review by the Office of Student Development and Welfare (OSDW).
                    Filing a false or malicious report is itself a disciplinary violation.
                </p>
            </div>
        </div>

        @error('certification')
            <p class="text-[#a50104] text-xs font-bold mb-4 block">{{ $message }}</p>
        @enderror

        <div class="flex justify-end gap-4 border-t border-gray-100 pt-6">
            <a
                href="{{ route('staff.dashboard') }}"
                class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-colors"
            >
                Cancel
            </a>
            <button
                wire:click="submitCharge"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75 cursor-not-allowed"
                class="px-8 py-3 bg-[#a50104] text-white rounded-xl font-bold hover:bg-[#590004] transition-colors shadow-lg flex items-center gap-2"
            >
                <svg wire:loading wire:target="submitCharge" class="animate-spin -ml-1 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="submitCharge">Submit Formal Charge</span>
                <span wire:loading wire:target="submitCharge">Submitting...</span>
            </button>
        </div>
    </div>

</div>
