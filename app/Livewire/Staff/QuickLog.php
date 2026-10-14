<?php

namespace App\Livewire\Staff;

use App\Events\IncidentReported;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
use App\Services\OffenseProgressionService;
use App\Services\OffenseRuleSuggestionService;
use App\Services\TribunalCaseRegistrar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class QuickLog extends Component
{
    public bool $showModal = false;

    public $studentId = '';

    #[Validate('required|exists:offense_rules,id')]
    public $offenseId = '';

    public string $violationSearch = '';

    public string $complainantName = '';

    #[Validate('required|string|min:10')]
    public $description = '';

    // Student identity verification
    public $studentInitials = '';

    public $studentProgram = '';

    public string $studentName = '';

    public string $studentCollege = '';

    public string $studentYearLevel = '';

    public string $studentSection = '';

    public $studentVerified = false;

    public int $offenseCount = 1;

    public string $offenseOrdinal = '1st';

    public ?string $nextSanction = null;

    /** @var array<string, mixed> */
    public array $offensePreview = [];

    // Available minor offenses (only)
    public $minorOffenses = [];

    /**
     * Mount the component and load minor offenses.
     */
    public function mount(): void
    {
        // Load only minor offenses for quick log
        $this->minorOffenses = OffenseRule::where('gravity', 'minor')
            ->orderBy('code')
            ->get();
    }

    /**
     * Open the quick log modal (can be called from parent or event).
     */
    #[On('open-quick-log-modal')]
    public function openModal(?int $offenseId = null, ?string $description = null, ?string $studentId = null, ?string $complainant = null): void
    {
        $this->showModal = true;
        $this->reset([
            'studentId',
            'offenseId',
            'violationSearch',
            'complainantName',
            'description',
            'studentInitials',
            'studentProgram',
            'studentName',
            'studentCollege',
            'studentYearLevel',
            'studentSection',
            'studentVerified',
            'offenseCount',
            'offenseOrdinal',
            'nextSanction',
            'offensePreview',
        ]);

        if ($studentId !== null) {
            $this->studentId = $studentId;
            $this->updatedStudentId();
            $this->dispatch('prefill-student-lookup', studentId: $studentId);
        } else {
            $this->dispatch('reset-student-lookup');
        }

        if ($offenseId !== null) {
            $offense = OffenseRule::query()->active()->where('gravity', 'minor')->findOrFail($offenseId);
            $this->offenseId = (string) $offense->id;
            $this->violationSearch = $offense->title;
            $this->refreshOffenseProgression();
        }

        if ($description !== null) {
            $this->description = $description;
        }

        if ($complainant !== null) {
            $this->complainantName = $complainant;
        }
    }

    /**
     * Close the quick log modal.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset([
            'studentId',
            'offenseId',
            'violationSearch',
            'complainantName',
            'description',
            'studentInitials',
            'studentProgram',
            'studentName',
            'studentCollege',
            'studentYearLevel',
            'studentSection',
            'studentVerified',
            'offenseCount',
            'offenseOrdinal',
            'nextSanction',
            'offensePreview',
        ]);
        $this->resetErrorBag();
    }

    /**
     * Auto-fetch identity verification when student ID is entered.
     */
    public function updatedStudentId(): void
    {
        $this->studentVerified = false;
        $this->studentInitials = '';
        $this->studentProgram = '';
        $this->studentName = '';
        $this->studentCollege = '';
        $this->studentYearLevel = '';
        $this->studentSection = '';
        $this->offenseId = '';
        $this->offensePreview = [];
        $this->offenseCount = 1;
        $this->offenseOrdinal = '1st';
        $this->nextSanction = null;

        if (empty($this->studentId)) {
            return;
        }

        $student = User::role('student')
            ->where('student_id', $this->studentId)
            ->first();

        if ($student) {
            // Generate initials from name
            $nameParts = explode(' ', $student->name);
            $this->studentInitials = strtoupper(
                substr($nameParts[0] ?? '', 0, 1).
                substr($nameParts[1] ?? '', 0, 1)
            );

            // Get program (you may need to adjust this based on your User model)
            $this->studentProgram = $student->program ?? 'Unknown Program';
            $this->studentName = $student->name;
            $this->studentCollege = $student->college ?? '';
            $this->studentYearLevel = $student->year_level ?? '';
            $this->studentSection = $student->section ?? '';

            $this->studentVerified = true;
        }
    }

    #[On('staff-student-selected')]
    public function useSelectedStudent(int $userId): void
    {
        $student = User::query()->role('student')->findOrFail($userId);
        $studentId = (string) $student->student_id;

        if ($this->studentId !== $studentId || ! $this->studentVerified) {
            $this->studentId = $studentId;
            $this->updatedStudentId();

            return;
        }

        $this->refreshOffenseProgression();
    }

    #[On('staff-student-lookup-cleared')]
    public function clearSelectedStudent(): void
    {
        $this->studentId = '';
        $this->updatedStudentId();
    }

    public function updatedOffenseId(): void
    {
        $this->refreshOffenseProgression();
    }

    public function updatedViolationSearch(): void
    {
        $suggestion = $this->getOffenseSuggestionsProperty()->first();

        if ($suggestion !== null && $suggestion->gravity === 'minor') {
            $this->offenseId = (string) $suggestion->id;
            $this->refreshOffenseProgression();
        }
    }

    public function selectOffenseSuggestion(int $offenseId): void
    {
        $offense = OffenseRule::query()->active()->findOrFail($offenseId);

        if (in_array($offense->severity_level, OffenseRule::TRIBUNAL_SEVERITY_LEVELS, true)) {
            $this->redirect(route('staff.formal-charge', ['offense' => $offense->id]), navigate: true);

            return;
        }

        if ($offense->gravity !== 'minor') {
            $this->addError('violationSearch', 'This offense must be filed as a Major Formal Charge.');

            return;
        }

        $this->offenseId = (string) $offense->id;
        $this->refreshOffenseProgression();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, OffenseRule>
     */
    public function getOffenseSuggestionsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return app(OffenseRuleSuggestionService::class)->suggest($this->violationSearch);
    }

    private function refreshOffenseProgression(): void
    {
        $this->offensePreview = [];
        $this->offenseCount = 1;
        $this->offenseOrdinal = '1st';
        $this->nextSanction = null;

        if (! $this->studentVerified || ! $this->offenseId) {
            return;
        }

        $student = User::role('student')
            ->where('student_id', $this->studentId)
            ->first();
        $offense = OffenseRule::query()
            ->active()
            ->where('gravity', 'minor')
            ->find($this->offenseId);

        if ($student === null || $offense === null) {
            return;
        }

        $preview = app(OffenseProgressionService::class)->preview($student->id, $offense);
        $this->offenseCount = $preview['next_count'];
        $this->offenseOrdinal = $this->formatOrdinal($this->offenseCount);
        $this->nextSanction = $preview['next_sanction'];
        $this->offensePreview = $preview;
    }

    private function formatOrdinal(int $number): string
    {
        $suffix = match (true) {
            $number % 100 >= 11 && $number % 100 <= 13 => 'th',
            $number % 10 === 1 => 'st',
            $number % 10 === 2 => 'nd',
            $number % 10 === 3 => 'rd',
            default => 'th',
        };

        return $number.$suffix;
    }

    /**
     * Submit the quick log report.
     */
    public function submit(): void
    {
        $validated = $this->validate([
            'studentId' => [
                'required',
                'string',
                'regex:/^\d{2}-\d{5}$/',
                Rule::exists('users', 'student_id')->where('role_type', 'student'),
            ],
            'offenseId' => [
                'required',
                Rule::exists('offense_rules', 'id')->where('gravity', 'minor')->where('is_active', true),
            ],
            'description' => ['required', 'string', 'min:10'],
            'complainantName' => ['nullable', 'string', 'max:255'],
        ], [
            'studentId.regex' => 'Student ID must follow the format 24-00005.',
            'studentId.exists' => 'The selected student ID is invalid.',
        ]);

        if (! $this->studentVerified) {
            $this->addError('studentId', 'Please verify the student identity before submitting.');

            return;
        }

        $student = User::role('student')
            ->where('student_id', $validated['studentId'])
            ->firstOrFail();

        // Create the report
        $report = IncidentReport::create([
            'tracking_number' => 'INC-'.date('Y').'-'.strtoupper(Str::random(5)),
            'reporter_id' => Auth::id(),
            'student_id' => $student->id,
            'offense_id' => $validated['offenseId'],
            'report_type' => 'Quick Log',
            'complainant_name' => $validated['complainantName'] ?? null,
            'description' => $validated['description'],
            'evidence_path' => null, // Quick logs don't require evidence
            'status' => 'Submitted',
        ]);

        // Trigger notification to OSDW
        Event::dispatch(new IncidentReported($report));
        app(TribunalCaseRegistrar::class)->registerEligibleReport($report);

        // Close modal and show success message
        $this->closeModal();
        $this->dispatch('incident-logged', trackingNumber: $report->tracking_number);

        // Refresh parent component
        $this->dispatch('refresh-dashboard');

        session()->flash('success', 'Minor infraction logged successfully. Tracking Number: '.$report->tracking_number);
    }

    public function render()
    {
        return view('livewire.staff.quick-log');
    }
}
