<x-staff-app-layout>
    <x-slot name="header">Student Roster</x-slot>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        @livewire('staff.student-roster')
    </div>

    {{-- Student add/edit modal sits outside the roster so Livewire 3 hydration
         works correctly. Events dispatched by the roster are global and reach here. --}}
    @livewire('staff.student-form')
</x-staff-app-layout>
