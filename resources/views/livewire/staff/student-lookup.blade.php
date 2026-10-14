<div class="space-y-4" data-testid="student-lookup">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="lookupStudentName" class="mb-1 block text-sm font-semibold text-gray-700">Student Name</label>
            <input
                id="lookupStudentName"
                type="search"
                wire:model.live.debounce.250ms="studentName"
                autocomplete="off"
                placeholder="Search by student name"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-[#590004] focus:ring-2 focus:ring-[#590004]"
            >
        </div>
        <div>
            <label for="lookupStudentId" class="mb-1 block text-sm font-semibold text-gray-700">Student ID</label>
            <input
                id="lookupStudentId"
                type="text"
                wire:model.live.debounce.300ms="studentId"
                x-on:input="$el.value = $el.value.replace(/\D/g, '').slice(0, 7).replace(/^(\d{2})(?=\d)/, '$1-')"
                inputmode="numeric"
                pattern="[0-9]{2}-[0-9]{5}"
                maxlength="8"
                autocomplete="off"
                placeholder="e.g., 24-00005"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 font-mono focus:border-[#590004] focus:ring-2 focus:ring-[#590004]"
            >
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label for="lookupCollegeFilter" class="mb-1 block text-sm font-semibold text-gray-700">Filter by College</label>
            <select
                id="lookupCollegeFilter"
                wire:model.live="collegeFilter"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 focus:border-[#590004] focus:ring-2 focus:ring-[#590004]"
            >
                <option value="">All Colleges</option>
                @foreach ($colleges as $college)
                    <option value="{{ $college }}">{{ $college }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="lookupProgramFilter" class="mb-1 block text-sm font-semibold text-gray-700">Filter by Program</label>
            <select
                id="lookupProgramFilter"
                wire:model.live="programFilter"
                @disabled($programs === [])
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 focus:border-[#590004] focus:ring-2 focus:ring-[#590004] disabled:bg-gray-100"
            >
                <option value="">All Programs</option>
                @foreach ($programs as $program)
                    <option value="{{ $program }}">{{ $program }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if (count($this->studentSuggestions) > 0)
        <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            @foreach ($this->studentSuggestions as $student)
                <button
                    type="button"
                    wire:key="student-suggestion-{{ $student->id }}"
                    wire:click="selectStudent({{ $student->id }})"
                    class="block w-full border-b border-gray-100 px-4 py-3 text-left last:border-b-0 hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
                >
                    <span class="block font-semibold text-gray-900">{{ $student->name }} — ID: {{ $student->student_id }}</span>
                    <span class="mt-1 block text-sm text-gray-600">
                        {{ $student->college ?: 'College not set' }} - {{ $student->program ?: 'Program not set' }}
                        {{ $student->year_level ? ' '.$student->year_level : '' }}
                        {{ $student->section ? ' '.$student->section : '' }}
                    </span>
                </button>
            @endforeach
        </div>
    @elseif (mb_strlen(trim($studentName)) >= 2)
        <p class="text-sm text-gray-500">No matching students. Try another name or adjust the college/program filters.</p>
    @endif

    @if ($studentResolved)
        <p class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            Student selected: <strong>{{ $studentName }}</strong> — {{ $collegeFilter }} / {{ $programFilter }}
        </p>
    @endif
</div>
