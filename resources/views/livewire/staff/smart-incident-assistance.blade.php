<section aria-labelledby="smart-incident-heading" class="space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#590004] text-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-3 4-3-4z"/>
                </svg>
            </div>
            <div>
                <h3 id="smart-incident-heading" class="text-lg font-bold text-[#250001] sm:text-xl">Smart Incident Assistance</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-600">
                    Search handbook rules or scan a complaint letter to prepare the right incident form. Review all extracted details before submitting.
                </p>
            </div>
        </div>

        <label class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#590004] bg-white px-4 py-3 text-sm font-semibold text-[#590004] shadow-sm transition-colors hover:bg-red-50 lg:w-auto">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Upload Complaint Letter Image</span>
            <input
                type="file"
                wire:model="complaintLetter"
                accept=".jpg,.jpeg,.png,.pdf"
                class="sr-only"
                aria-label="Upload complaint letter image or PDF"
            >
        </label>
    </div>

    @error('complaintLetter')
        <p class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <div wire:loading wire:target="complaintLetter" class="flex items-center gap-2 text-sm font-medium text-[#590004]">
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        Scanning complaint letter…
    </div>

    <div class="relative">
        <label for="smart-offense-search" class="sr-only">Search offense rules</label>
        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input
            id="smart-offense-search"
            type="search"
            wire:model.live.debounce.300ms="search"
            autocomplete="off"
            placeholder="Search offense rule or keyword, e.g., ‘NO ID’, ‘cheating’"
            class="w-full rounded-xl border border-gray-300 bg-white py-3.5 pl-12 pr-4 text-sm text-gray-900 shadow-sm transition focus:border-[#590004] focus:outline-none focus:ring-2 focus:ring-[#590004]/20"
        >
    </div>

    @if($this->offenseSuggestions->isNotEmpty())
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-label="Matching handbook offenses">
            <ul class="divide-y divide-gray-100">
                @foreach($this->offenseSuggestions as $offense)
                    @php($isMajor = $offense->requiresTribunalReview() || $offense->gravity !== 'minor')
                    <li wire:key="smart-incident-offense-{{ $offense->id }}">
                        <button
                            type="button"
                            wire:click="selectOffense({{ $offense->id }})"
                            class="flex w-full flex-col gap-2 px-4 py-3 text-left transition-colors hover:bg-gray-50 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-gray-900">{{ $offense->code }} — {{ $offense->title }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500">{{ $offense->category }}</span>
                            </span>
                            <span @class([
                                'shrink-0 self-start rounded-full px-2.5 py-1 text-xs font-bold tracking-wide sm:self-auto',
                                'bg-red-100 text-red-800' => $isMajor,
                                'bg-amber-100 text-amber-800' => ! $isMajor,
                            ])>
                                {{ $isMajor ? '[MAJOR]' : '[MINOR]' }}
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @elseif(mb_strlen(trim($search)) >= 2 && $extractedText === '')
        <p class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">No matching active handbook offenses were found.</p>
    @endif

    @if($extractedText !== '')
        <div class="space-y-4 rounded-xl border border-blue-200 bg-blue-50 p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h4 class="font-bold text-blue-950">Complaint letter scanned</h4>
                    <p class="mt-1 text-sm text-blue-900">{{ $ocrNotice }}</p>
                    <p class="mt-2 text-xs text-blue-800">
                        @if($extractedStudentId)
                            Student ID found: <strong class="font-mono">{{ $extractedStudentId }}</strong>. Student details will be verified in the filing form.
                        @else
                            No formatted Student ID was detected; enter and verify the student in the filing form.
                        @endif
                        @if($extractedComplainant)
                            Complainant detected: <strong>{{ $extractedComplainant }}</strong>.
                        @else
                            No complainant name was detected.
                        @endif
                        The signed-in staff member will be recorded as the reporter.
                    </p>
                </div>
                @if($suggestedOffenseId)
                    @php($suggestedOffense = $this->offenseSuggestions->firstWhere('id', $suggestedOffenseId))
                    @if($suggestedOffense)
                        @php($suggestedMajor = $suggestedOffense->requiresTribunalReview() || $suggestedOffense->gravity !== 'minor')
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-1 text-xs font-bold tracking-wide',
                            'bg-red-100 text-red-800' => $suggestedMajor,
                            'bg-amber-100 text-amber-800' => ! $suggestedMajor,
                        ])>
                            Suggested {{ $suggestedMajor ? '[MAJOR]' : '[MINOR]' }}
                        </span>
                    @endif
                @endif
            </div>

            <details class="rounded-lg border border-blue-200 bg-white">
                <summary class="cursor-pointer px-3 py-2 text-sm font-semibold text-blue-950">Review extracted complaint text</summary>
                <pre class="max-h-48 overflow-y-auto whitespace-pre-wrap break-words border-t border-blue-100 px-3 py-2 text-xs text-gray-700">{{ $extractedText }}</pre>
            </details>

            @if($suggestedOffenseId && $this->offenseSuggestions->contains('id', $suggestedOffenseId))
                <button
                    type="button"
                    wire:click="continueWithSuggestion"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-[#590004] px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-[#a50104] sm:w-auto"
                >
                    Continue with suggested offense
                </button>
            @endif
        </div>
    @endif
</section>
