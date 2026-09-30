<?php

namespace App\Livewire\Staff;

use App\Helpers\StudentProgramCatalog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StudentForm extends Component
{
    public string $mode = 'create';

    public ?int $studentId = null;

    public bool $isEditing = false;

    public ?int $editingId = null;

    public string $studentIdInput = '';

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $college = '';

    public string $program = '';

    /** @var list<string> */
    public array $programs = [];

    public string $yearLevel = '';

    public string $section = '';

    public bool $studentIdTaken = false;

    /** @var list<string> */
    public array $colleges = [];

    /** @var list<string> */
    public array $yearLevels = [
        '1st Year',
        '2nd Year',
        '3rd Year',
        '4th Year',
    ];

    /**
     * Mount the component with the mode and optional student ID.
     */
    public function mount(string $mode = 'create', ?int $studentId = null): void
    {
        $this->colleges = StudentProgramCatalog::colleges();
        $this->mode = $mode;
        $this->studentId = $studentId;
        $this->isEditing = $mode === 'edit';

        if ($this->isEditing && $studentId !== null) {
            $student = User::role('student')->findOrFail($studentId);
            $this->editingId = $student->id;
            $this->studentIdInput = $student->student_id ?? '';
            $this->firstName = $student->first_name ?? '';
            $this->lastName = $student->last_name ?? '';
            $this->email = $student->email;
            $this->college = $student->college ?? '';
            $this->program = $student->program ?? '';
            $this->programs = StudentProgramCatalog::programsForCollege($this->college);
            $this->yearLevel = $student->year_level ?? '';
            $this->section = $student->section ?? '';
        }
    }

    /**
     * Navigate back to the student roster.
     */
    public function cancel(): void
    {
        $this->redirect(route('staff.students'), navigate: true);
    }

    /**
     * Check uniqueness of the student ID input in real time.
     */
    public function updatedStudentIdInput(): void
    {
        $this->studentIdTaken = false;

        if (empty($this->studentIdInput)) {
            return;
        }

        $query = User::where('student_id', $this->studentIdInput);

        if ($this->isEditing && $this->editingId !== null) {
            $query->where('id', '!=', $this->editingId);
        }

        $this->studentIdTaken = $query->exists();
    }

    public function updatedCollege(): void
    {
        $this->program = '';
        $this->programs = StudentProgramCatalog::programsForCollege($this->college);
        $this->resetValidation('program');
    }

    /**
     * Save the student record (create or update).
     */
    public function save(): void
    {
        $rules = [
            'firstName' => 'required|string|max:100',
            'lastName' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'college' => ['required', 'string', Rule::in(StudentProgramCatalog::colleges())],
            'program' => ['required', 'string', Rule::in(StudentProgramCatalog::programsForCollege($this->college))],
            'yearLevel' => 'required|string|in:'.implode(',', $this->yearLevels),
            'section' => 'required|string|max:10',
        ];

        if ($this->isEditing) {
            $rules['studentIdInput'] = 'required|string|max:50|unique:users,student_id,'.$this->editingId;
            $rules['email'] = 'required|email|max:255|unique:users,email,'.$this->editingId;
        } else {
            $rules['studentIdInput'] = 'required|string|max:50|unique:users,student_id';
            $rules['email'] = 'required|email|max:255|unique:users,email';
        }

        $this->validate($rules, [
            'firstName.required' => 'First name is required.',
            'lastName.required' => 'Last name is required.',
            'studentIdInput.required' => 'Student ID is required.',
            'studentIdInput.unique' => 'This Student ID is already registered.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email is already in use.',
            'college.required' => 'Please select a college.',
            'college.in' => 'Invalid college selected.',
            'program.required' => 'Please select a program.',
            'program.in' => 'The selected program does not belong to this college.',
            'yearLevel.required' => 'Year level is required.',
            'yearLevel.in' => 'Invalid year level selected.',
            'section.required' => 'Section is required.',
        ]);

        $fullName = trim($this->firstName.' '.$this->lastName);

        if ($this->isEditing && $this->editingId !== null) {
            $student = User::role('student')->findOrFail($this->editingId);

            $student->update([
                'student_id' => $this->studentIdInput,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'name' => $fullName,
                'email' => $this->email,
                'college' => $this->college,
                'program' => $this->program,
                'role_type' => 'student',
                'year_level' => $this->yearLevel,
                'section' => $this->section,
            ]);

            session()->flash('roster-success', 'Student record updated successfully.');
        } else {
            $plainPassword = $this->studentIdInput ?: Str::random(10);

            $student = User::create([
                'student_id' => $this->studentIdInput,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'name' => $fullName,
                'email' => $this->email,
                'college' => $this->college,
                'program' => $this->program,
                'role_type' => 'student',
                'year_level' => $this->yearLevel,
                'section' => $this->section,
                'password' => Hash::make($plainPassword),
                'email_verified_at' => now(),
            ]);

            $student->assignRole('student');

            \Illuminate\Support\Facades\Mail::to($student->email)->queue(new \App\Mail\StudentWelcomeMail($student, $plainPassword));

            session()->flash('roster-success', "Student {$fullName} registered successfully. Their temporary password is their Student ID.");
        }

        $this->redirect(route('staff.students'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.staff.student-form');
    }
}
