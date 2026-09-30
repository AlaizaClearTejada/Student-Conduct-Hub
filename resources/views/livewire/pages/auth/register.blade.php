<?php

use App\Helpers\StudentProgramCatalog;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $student_id = '';

    public string $college = '';

    public string $program = '';

    public string $year_level = '';

    public string $section = '';

    /** @var list<string> */
    public array $colleges = [];

    /** @var list<string> */
    public array $programs = [];

    public function mount(): void
    {
        $this->colleges = StudentProgramCatalog::colleges();
    }

    public function updatedStudentId(): void
    {
        $this->resetValidation('student_id');

        if ($this->student_id !== '' && ! preg_match('/^\d{2}-\d{5}$/', $this->student_id)) {
            $this->addError('student_id', 'Student ID must follow the format 00-00000');
        }
    }

    public function updatedCollege(): void
    {
        $this->program = '';
        $this->programs = StudentProgramCatalog::programsForCollege($this->college);
        $this->resetValidation('program');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'student_id' => ['required', 'string', 'regex:/^\d{2}-\d{5}$/', 'unique:users,student_id'],
            'college' => ['required', 'string', Rule::in(StudentProgramCatalog::colleges())],
            'program' => ['required', 'string', Rule::in(StudentProgramCatalog::programsForCollege($this->college))],
            'year_level' => ['required', Rule::in(['1st Year', '2nd Year', '3rd Year', '4th Year'])],
            'section' => ['required', 'string', 'max:10'],
        ];

        $validated = $this->validate($rules, [
            'student_id.regex' => 'The Student ID format must be strictly 00-00000 (e.g., 24-00001).',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role_type'] = 'student';

        $user = User::create($validated);
        $user->assignRole('student');

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('student.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="student_id" :value="__('Student ID')" />
            <x-text-input wire:model.live.debounce.300ms="student_id" id="student_id" class="block mt-1 w-full" type="text" name="student_id" required inputmode="numeric" pattern="[0-9]{2}-[0-9]{5}" maxlength="8" placeholder="e.g., 24-00001" autocomplete="off" />
            <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="college" :value="__('College')" />
            <select wire:model.live="college" id="college" name="college" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select College</option>
                @foreach ($colleges as $availableCollege)
                    <option value="{{ $availableCollege }}">{{ $availableCollege }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('college')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="program" :value="__('Program')" />
            <select wire:model="program" id="program" name="program" required @disabled(! $college || empty($programs)) class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500">
                <option value="">Select Program</option>
                @foreach ($programs as $availableProgram)
                    <option value="{{ $availableProgram }}">{{ $availableProgram }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('program')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="year_level" :value="__('Year Level')" />
            <select wire:model="year_level" id="year_level" name="year_level" required class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Year Level</option>
                @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $yearLevelOption)
                    <option value="{{ $yearLevelOption }}">{{ $yearLevelOption }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="section" :value="__('Section')" />
            <x-text-input wire:model="section" id="section" class="block mt-1 w-full" type="text" name="section" required maxlength="10" />
            <x-input-error :messages="$errors->get('section')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
