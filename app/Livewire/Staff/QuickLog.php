<?php

namespace App\Livewire\Staff;

use App\Events\IncidentReported;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
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

    #[Validate('required|string|min:10')]
    public $description = '';

    // Student identity verification
    public $studentInitials = '';

    public $studentProgram = '';

    public $studentVerified = false;

    public int $offenseCount = 1;

    public string $offenseOrdinal = '1st';

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
    public function openModal(): void
    {
        $this->showModal = true;
        $this->reset(['studentId', 'offenseId', 'description', 'studentInitials', 'studentProgram', 'studentVerified']);
    }

    /**
     * Close the quick log modal.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['studentId', 'offenseId', 'description', 'studentInitials', 'studentProgram', 'studentVerified']);
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

            // Calculate offense count (previous reports + 1)
            $previousCount = IncidentReport::where('student_id', $student->id)->count();
            $this->offenseCount = $previousCount + 1;
            $this->offenseOrdinal = $this->formatOrdinal($this->offenseCount);

            $this->studentVerified = true;
        }
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
            'offenseId' => ['required', 'exists:offense_rules,id'],
            'description' => ['required', 'string', 'min:10'],
        ], [
            'studentId.regex' => 'Student ID must follow the format 00-00000.',
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
            'description' => $validated['description'],
            'evidence_path' => null, // Quick logs don't require evidence
            'status' => 'Submitted',
        ]);

        // Trigger notification to OSDW
        Event::dispatch(new IncidentReported($report));

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
