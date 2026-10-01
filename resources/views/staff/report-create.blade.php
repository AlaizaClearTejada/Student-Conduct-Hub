<x-staff-app-layout>
    <x-slot name="header">
        {{ request('type') === 'quick' ? 'Log Minor Infraction' : 'File Formal Charge' }}
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-[#250001] mb-2">
                    {{ request('type') === 'quick' ? 'Quick Log Entry' : 'Formal Charge Report' }}
                </h2>
                <p class="text-gray-600">
                    {{ request('type') === 'quick' 
                        ? 'For minor infractions such as uniform violations, missing ID, or tardiness.' 
                        : 'For major offenses requiring detailed documentation and evidence.' }}
                </p>
            </div>

            <form method="POST" action="{{ route('staff.report.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <input type="hidden" name="report_type" value="{{ request('type') === 'quick' ? 'Quick Log' : 'Formal Charge' }}">

                <div>
                    <label for="student_id" class="block text-sm font-bold text-gray-700 mb-2">
                        Student <span class="text-[#a50104]">*</span>
                    </label>
                    <select id="student_id" name="student_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#590004] focus:border-transparent transition-all @error('student_id') border-red-500 @enderror">
                        <option value="">Select a student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->student_id }} - {{ $student->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="offense_id" class="block text-sm font-bold text-gray-700 mb-2">
                        Offense Type <span class="text-[#a50104]">*</span>
                    </label>
                    <select id="offense_id" name="offense_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#590004] focus:border-transparent transition-all @error('offense_id') border-red-500 @enderror">
                        <option value="">Select an offense...</option>
                        @foreach($offenseRules as $offense)
                            <option value="{{ $offense->id }}" {{ old('offense_id') == $offense->id ? 'selected' : '' }}>
                                {{ $offense->code }} - {{ $offense->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('offense_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 mb-2">
                        Incident Description <span class="text-[#a50104]">*</span>
                    </label>
                    <textarea id="description" name="description" rows="6" required
                              placeholder="Provide a detailed description of the incident, including date, time, location, and any relevant circumstances..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#590004] focus:border-transparent transition-all resize-none @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Minimum 10 characters</p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(request('type') === 'formal')
                    <div x-data="{ 
                        fileName: '', 
                        dragging: false,
                        handleFiles(files) {
                            if (files.length > 0) {
                                this.fileName = files[0].name;
                            }
                        }
                    }">
                        <label for="evidence" class="block text-sm font-bold text-gray-700 mb-2">
                            Evidence Upload <span class="text-[#a50104]">*</span>
                        </label>
                        <div 
                            @drop.prevent="dragging = false; handleFiles($event.dataTransfer.files); $refs.fileInput.files = $event.dataTransfer.files"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            :class="dragging ? 'border-[#a50104] bg-red-50' : 'border-gray-300'"
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-lg transition-all cursor-pointer"
                            @click="$refs.fileInput.click()"
                        >
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12" :class="dragging ? 'text-[#a50104]' : 'text-gray-400'" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <span class="font-medium" :class="dragging ? 'text-[#a50104]' : 'text-[#590004]'">
                                        <span x-show="!fileName">Upload a file or drag and drop</span>
                                        <span x-show="fileName" x-text="fileName" class="text-green-600"></span>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">JPG, PNG, PDF up to 5MB</p>
                                <input 
                                    x-ref="fileInput"
                                    id="evidence" 
                                    name="evidence" 
                                    type="file" 
                                    class="sr-only" 
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    @change="handleFiles($event.target.files)"
                                    required
                                >
                            </div>
                        </div>
                        @error('evidence')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-600 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <strong>Note:</strong> Evidence is required for formal charges. This may include photos of vandalism, screenshots of cyberbullying, or other relevant documentation.
                        </p>
                    </div>
                @else
                    <div>
                        <label for="evidence" class="block text-sm font-bold text-gray-700 mb-2">
                            Evidence Upload <span class="text-gray-500">(Optional)</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-[#590004] transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="evidence" class="relative cursor-pointer bg-white rounded-md font-medium text-[#590004] hover:text-[#a50104] focus-within:outline-none">
                                        <span>Upload a file</span>
                                        <input id="evidence" name="evidence" type="file" class="sr-only" accept=".jpg,.jpeg,.png,.pdf">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">JPG, PNG, PDF up to 5MB</p>
                            </div>
                        </div>
                        @error('evidence')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-bold text-gray-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#590004]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Privacy & Confidentiality Notice
                    </h4>
                    <p class="text-sm text-gray-600">
                        This report will be submitted to the Office of Student Discipline and Welfare (OSDW) for review. 
                        You will be able to track the status of this report, but full resolution details and the student's complete disciplinary history 
                        remain confidential in accordance with CSU Student Manual Section G.20.
                    </p>
                </div>

                @if(request('type') === 'formal')
                    <!-- Certification Checkbox for Formal Charges -->
                    <div class="bg-[#fff5f5] border-2 border-[#a50104] rounded-lg p-4" x-data="{ certified: false }">
                        <label class="flex items-start cursor-pointer group">
                            <input 
                                type="checkbox" 
                                name="certification" 
                                required 
                                x-model="certified"
                                class="mt-1 form-checkbox h-5 w-5 text-[#a50104] rounded border-gray-300 focus:ring-[#a50104] focus:ring-2 transition-colors"
                            >
                            <span class="ml-3 text-sm">
                                <span class="font-bold text-gray-900 block mb-1">Certification Required</span>
                                <span class="text-gray-700">
                                    I certify that this report is <strong>accurate</strong> and filed in <strong>good faith</strong>. 
                                    I understand that filing a false report may result in disciplinary action against me as a staff member.
                                </span>
                            </span>
                        </label>
                        @error('certification')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('staff.dashboard') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-[#a50104] text-white rounded-lg font-bold hover:bg-[#590004] transition-colors shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ request('type') === 'formal' ? 'Submit Formal Charge' : 'Submit Report' }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-staff-app-layout>
