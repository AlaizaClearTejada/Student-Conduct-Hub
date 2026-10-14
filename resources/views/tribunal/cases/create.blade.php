<x-admin-layout>
    <x-slot name="title">Create Tribunal Case</x-slot>
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-mahogany">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <a href="{{ route('tribunal.cases.index') }}" class="hover:text-mahogany">Tribunal Cases</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-gray-900 font-medium">New Case</span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">File a New Tribunal Case</h3>
            <p class="text-sm text-gray-500 mt-1">This form records a formal charge for confidential Tribunal review. Minor quick logs are not accepted.</p>
        </div>

        <form action="{{ route('tribunal.cases.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Case Number -->
            <div>
                <label for="case_number" class="block text-sm font-medium text-gray-700">Case Tracking Number</label>
                <input type="text" name="case_number" id="case_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm" placeholder="e.g. TR-2026-001" value="{{ old('case_number') }}" required>
                @error('case_number')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Case Title</label>
                <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm" placeholder="Brief summary of the incident" value="{{ old('title') }}" required>
                @error('title')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Initial Status</label>
                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm" required>
                    <option value="{{ \App\Enums\CaseStatus::UNDER_INVESTIGATION->value }}" selected>
                        {{ ucwords(str_replace('_', ' ', \App\Enums\CaseStatus::UNDER_INVESTIGATION->value)) }}
                    </option>
                </select>
                @error('status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Respondent Name -->
            <div>
                <label for="respondent_name" class="block text-sm font-medium text-gray-700">Respondent Name (Student)</label>
                <input type="text" name="respondent_name" id="respondent_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm" placeholder="e.g. John Doe" value="{{ old('respondent_name') }}">
                @error('respondent_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Incident Date -->
                <div>
                    <label for="incident_date" class="block text-sm font-medium text-gray-700">Incident Date</label>
                    <input type="date" name="incident_date" id="incident_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm" value="{{ old('incident_date') }}">
                    @error('incident_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority Level</label>
                    <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm" required>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low Priority</option>
                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal Priority</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High Priority</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Case Category</label>
                <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm">
                    <option value="">-- Select a Category --</option>
                    <option value="Academic Dishonesty" {{ old('category') == 'Academic Dishonesty' ? 'selected' : '' }}>Academic Dishonesty</option>
                    <option value="Behavioral Misconduct" {{ old('category') == 'Behavioral Misconduct' ? 'selected' : '' }}>Behavioral Misconduct</option>
                    <option value="Property Damage" {{ old('category') == 'Property Damage' ? 'selected' : '' }}>Property Damage</option>
                    <option value="Safety Violation" {{ old('category') == 'Safety Violation' ? 'selected' : '' }}>Safety Violation</option>
                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('category')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description</label>
                <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-mahogany focus:ring-mahogany sm:text-sm" placeholder="Provide full details regarding the case.">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('tribunal.cases.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mahogany">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-mahogany border border-transparent rounded-md shadow-sm hover:bg-black-cherry focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-mahogany">
                    Create Case
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
