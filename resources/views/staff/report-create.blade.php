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

            <form
                method="POST"
                action="{{ route('staff.report.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
                x-data="incidentReportForm(@js($offenseRules->map(fn ($offense) => [
                    'id' => $offense->id,
                    'code' => $offense->code,
                    'title' => $offense->title,
                    'category' => $offense->category,
                    'description' => $offense->description,
                    'gravity' => $offense->gravity,
                    'severity' => $offense->severity_level,
                ])->values()))"
            >
                @csrf

                <input type="hidden" name="report_type" x-bind:value="reportType">

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
                    <label for="violation-search" class="block text-sm font-bold text-gray-700 mb-2">
                        Search Handbook Violations
                    </label>
                    <input
                        id="violation-search"
                        type="search"
                        x-model="search"
                        placeholder="Try keywords like NO ID, tardiness, or academic dishonesty"
                        autocomplete="off"
                        class="w-full px-4 py-3 mb-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#590004] focus:border-transparent transition-all"
                    >
                    <div x-show="search.trim().length >= 2" x-cloak class="mb-3 overflow-hidden rounded-lg border border-gray-200 divide-y divide-gray-200">
                        <template x-for="suggestion in suggestions()" :key="suggestion.id">
                            <button
                                type="button"
                                x-on:click="selectRule(suggestion)"
                                class="w-full px-4 py-3 text-left hover:bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2"
                            >
                                <span>
                                    <span class="block font-semibold text-gray-900" x-text="`${suggestion.code} — ${suggestion.title}`"></span>
                                    <span class="block text-xs text-gray-500" x-text="suggestion.category"></span>
                                </span>
                                <span
                                    class="self-start sm:self-auto rounded-full px-2.5 py-1 text-xs font-medium"
                                    x-bind:class="classification(suggestion) === 'Formal Charge' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'"
                                    x-text="classification(suggestion) === 'Formal Charge' ? 'Major Formal Charge' : 'Minor Infraction'"
                                ></span>
                            </button>
                        </template>
                        <p x-show="suggestions().length === 0" class="px-4 py-3 text-sm text-gray-500">No matching handbook violations found.</p>
                    </div>
                    <p class="mb-2 text-xs text-gray-500">
                        Selected filing type: <strong x-text="reportType === 'Formal Charge' ? 'Major Formal Charge' : 'Minor Infraction'"></strong>
                    </p>

                    <label for="offense_id" class="block text-sm font-bold text-gray-700 mb-2">
                        Offense Type <span class="text-[#a50104]">*</span>
                    </label>
                    <select id="offense_id" name="offense_id" x-ref="offenseSelect" required
                            x-on:change="selectedRule = offenses.find(offense => offense.id === Number($event.target.value)) || null; if (selectedRule) reportType = classification(selectedRule)"
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

                <div id="offense-history-preview" data-history-url="{{ route('staff.offense-history') }}" aria-live="polite"></div>

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

                <div x-show="reportType === 'Formal Charge'" x-cloak>
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
                                    x-bind:required="reportType === 'Formal Charge'"
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
                </div>
                <div x-show="reportType !== 'Formal Charge'" x-cloak>
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
                </div>

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

                <div x-show="reportType === 'Formal Charge'" x-cloak>
                    <!-- Certification Checkbox for Formal Charges -->
                    <div class="bg-[#fff5f5] border-2 border-[#a50104] rounded-lg p-4" x-data="{ certified: false }">
                        <label class="flex items-start cursor-pointer group">
                            <input 
                                type="checkbox" 
                                name="certification" 
                                x-bind:required="reportType === 'Formal Charge'"
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
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('staff.dashboard') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-[#a50104] text-white rounded-lg font-bold hover:bg-[#590004] transition-colors shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-text="reportType === 'Formal Charge' ? 'Submit Formal Charge' : 'Submit Quick Log'"></span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        function incidentReportForm(offenses) {
            return {
                offenses,
                search: '',
                selectedRule: offenses.find((offense) => offense.id === Number(@js(old('offense_id')))) || null,
                reportType: @js(request('type') === 'quick' ? 'Quick Log' : 'Formal Charge'),
                init() {
                    if (this.selectedRule) {
                        this.reportType = this.classification(this.selectedRule);
                    }
                },
                classification(offense) {
                    const severity = (offense.severity || '').toLowerCase();

                    if (severity === 'major' || severity === 'severe') {
                        return 'Formal Charge';
                    }

                    return severity === 'minor' || offense.gravity === 'minor'
                        ? 'Quick Log'
                        : 'Formal Charge';
                },
                suggestions() {
                    const terms = this.search.trim().toLowerCase().split(/\s+/).filter(Boolean);

                    if (terms.length === 0) {
                        return [];
                    }

                    return this.offenses
                        .map((offense) => ({
                            ...offense,
                            score: terms.reduce((score, term) => {
                                const searchable = `${offense.code} ${offense.title} ${offense.category} ${offense.description}`.toLowerCase();

                                return score + (searchable.includes(term) ? 1 : 0);
                            }, 0),
                        }))
                        .filter((offense) => offense.score > 0)
                        .sort((first, second) => second.score - first.score || first.title.localeCompare(second.title))
                        .slice(0, 8);
                },
                selectRule(offense) {
                    this.selectedRule = offense;
                    this.reportType = this.classification(offense);
                    this.$refs.offenseSelect.value = offense.id;
                    this.$refs.offenseSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    this.search = '';
                },
            };
        }

        document.addEventListener('DOMContentLoaded', () => {
            const studentSelect = document.getElementById('student_id');
            const offenseSelect = document.getElementById('offense_id');
            const preview = document.getElementById('offense-history-preview');

            const showMessage = (message, className = 'text-gray-600') => {
                preview.replaceChildren();
                const paragraph = document.createElement('p');
                paragraph.className = `rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm ${className}`;
                paragraph.textContent = message;
                preview.append(paragraph);
            };

            const loadHistory = async () => {
                if (!studentSelect.value || !offenseSelect.value) {
                    preview.replaceChildren();
                    return;
                }

                showMessage('Loading prior offense history…');

                try {
                    const url = new URL(preview.dataset.historyUrl, window.location.origin);
                    url.searchParams.set('student_id', studentSelect.value);
                    url.searchParams.set('offense_id', offenseSelect.value);
                    const response = await fetch(url, { headers: { Accept: 'application/json' } });

                    if (!response.ok) {
                        throw new Error(`History request failed (${response.status}).`);
                    }

                    const data = await response.json();
                    preview.replaceChildren();
                    const heading = document.createElement('p');
                    heading.className = 'rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950';
                    heading.textContent = `Prior occurrences: ${data.records.length}. Next level: ${data.next_level}. Mapped sanction: ${data.next_sanction || 'No tier configured'}.`;
                    preview.append(heading);

                    for (const record of data.records) {
                        const item = document.createElement('p');
                        item.className = 'mt-2 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700';
                        item.textContent = `${record.offense_level} · ${record.incident_date} · ${record.status} · ${record.sanction || 'Sanction not assigned'}`;
                        preview.append(item);
                    }
                } catch (error) {
                    showMessage(error instanceof Error ? error.message : 'Unable to load offense history.', 'text-red-700');
                }
            };

            studentSelect.addEventListener('change', loadHistory);
            offenseSelect.addEventListener('change', loadHistory);
            loadHistory();
        });
    </script>
</x-staff-app-layout>
