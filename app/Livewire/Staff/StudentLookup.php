<?php

namespace App\Livewire\Staff;

use App\Helpers\StudentProgramCatalog;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class StudentLookup extends Component
{
    public string $studentName = '';

    public string $studentId = '';

    public string $collegeFilter = '';

    public string $programFilter = '';

    public bool $studentResolved = false;

    /** @var list<string> */
    public array $colleges = [];

    /** @var list<string> */
    public array $programs = [];

    public function mount(?string $initialStudentId = null, ?string $college = null, ?string $program = null): void
    {
        $this->colleges = StudentProgramCatalog::colleges();
        $this->collegeFilter = $college ?? '';
        $this->programFilter = $program ?? '';
        $this->programs = StudentProgramCatalog::programsForCollege($this->collegeFilter);

        if ($initialStudentId !== null && preg_match('/^\d{2}-\d{5}$/', $initialStudentId)) {
            $this->studentId = $initialStudentId;
            $this->resolveByStudentId();
        }
    }

    public function updatedStudentName(): void
    {
        $this->studentResolved = false;
        $this->studentId = '';
        $this->dispatch('staff-student-lookup-cleared');
    }

    public function updatedStudentId(): void
    {
        $this->studentResolved = false;
        $this->studentName = '';
        $this->dispatch('staff-student-lookup-cleared');

        if (! preg_match('/^\d{2}-\d{5}$/', $this->studentId)) {
            return;
        }

        $this->resolveByStudentId();
    }

    public function updatedCollegeFilter(): void
    {
        $this->programFilter = '';
        $this->programs = StudentProgramCatalog::programsForCollege($this->collegeFilter);
    }

    public function selectStudent(int $studentId): void
    {
        $student = $this->studentsQuery()->findOrFail($studentId);
        $this->fillStudent($student);
    }

    #[On('reset-student-lookup')]
    public function resetLookup(): void
    {
        $this->reset(['studentName', 'studentId', 'collegeFilter', 'programFilter', 'studentResolved']);
        $this->colleges = StudentProgramCatalog::colleges();
        $this->programs = [];
    }

    #[On('prefill-student-lookup')]
    public function prefillStudent(?string $studentId = null): void
    {
        if ($studentId === null || ! preg_match('/^\d{2}-\d{5}$/', $studentId)) {
            return;
        }

        $this->studentId = $studentId;
        $this->resolveByStudentId();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getStudentSuggestionsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        $name = trim($this->studentName);

        if (mb_strlen($name) < 2) {
            return new \Illuminate\Database\Eloquent\Collection;
        }

        return $this->studentsQuery()
            ->where('name', 'like', '%'.$name.'%')
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.staff.student-lookup');
    }

    private function resolveByStudentId(): void
    {
        $student = User::query()
            ->role('student')
            ->where('student_id', $this->studentId)
            ->first();

        if ($student !== null) {
            $this->fillStudent($student);
        }
    }

    private function fillStudent(User $student): void
    {
        $this->studentId = (string) $student->student_id;
        $this->studentName = $student->name;
        $this->collegeFilter = (string) ($student->college ?? '');
        $this->programFilter = (string) ($student->program ?? '');
        $this->programs = StudentProgramCatalog::programsForCollege($this->collegeFilter);
        $this->studentResolved = true;
        $this->dispatch('staff-student-selected', userId: $student->id);
    }

    private function studentsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query()
            ->role('student')
            ->when($this->collegeFilter !== '', fn ($query) => $query->where('college', $this->collegeFilter))
            ->when($this->programFilter !== '', fn ($query) => $query->where('program', $this->programFilter));
    }
}
