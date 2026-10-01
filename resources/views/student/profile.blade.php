<x-student-app-layout>
    <x-slot name="header">
        My Profile
    </x-slot>

    <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-6">

        {{-- Student Information Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-[#250001] px-4 sm:px-8 py-6 flex items-center gap-4 sm:gap-6">
                <div class="w-20 h-20 rounded-full bg-[#f3f3f3] text-[#250001] flex items-center justify-center font-extrabold text-2xl shadow-lg flex-shrink-0">
                    {{ strtoupper(substr($user->first_name ?? $user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name ?? '', 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#f3f3f3]">
                        {{ $user->first_name && $user->last_name ? $user->first_name . ' ' . $user->last_name : $user->name }}
                    </h2>
                    <p class="text-sm text-gray-300 mt-0.5">{{ $user->email }}</p>
                    <span class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                        {{ $conductStanding === 'Good Standing' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <span class="w-2 h-2 rounded-full {{ $conductStanding === 'Good Standing' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        {{ $conductStanding }}
                    </span>
                </div>
            </div>

            <div class="px-4 sm:px-8 py-6 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-5">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Student ID</p>
                    <p class="text-sm font-medium text-gray-800">{{ $user->student_id ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Email Address</p>
                    <p class="text-sm font-medium text-gray-800">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Program</p>
                    <p class="text-sm font-medium text-gray-800">{{ $user->program ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">College</p>
                    <p class="text-sm font-medium text-gray-800">{{ $user->college ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Year Level</p>
                    <p class="text-sm font-medium text-gray-800">{{ $user->year_level ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Section</p>
                    <p class="text-sm font-medium text-gray-800">{{ $user->section ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Conduct Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                <p class="text-2xl font-bold {{ $activeSanctionsCount > 0 ? 'text-[#a50104]' : 'text-gray-400' }}">
                    {{ $activeSanctionsCount }}
                </p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Active Sanctions</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                <p class="text-2xl font-bold {{ $pendingCount > 0 ? 'text-yellow-600' : 'text-gray-400' }}">
                    {{ $pendingCount }}
                </p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Pending Review</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                <p class="text-2xl font-bold text-gray-700">{{ $resolvedCount }}</p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Resolved</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 text-center">
                <p class="text-2xl font-bold text-gray-700">{{ $totalRecords }}</p>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">Total Records</p>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-8">
            <livewire:profile.update-password-form />
        </div>

    </div>
</x-student-app-layout>
