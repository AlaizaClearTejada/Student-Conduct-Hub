<?php

namespace App\Livewire\Staff;

use App\Events\IncidentReported;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
use App\Services\OffenseProgressionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormalChargeForm extends Component
{
    use WithFileUploads;

    // Student lookup input (the string ID the staff types)
    public string $studentIdInput = '';

    // Resolved database primary key after lookup
    public ?int $studentDbId = null;

    // Auto-populated student fields
    public string $studentName = '';

    public string $college = '';

    public string $yearLevel = '';

    public string $section = '';

    public string $email = '';

    public bool $studentVerified = false;

    public int $offenseCount = 1;

    public ?string $nextSanction = null;

    /** @var array<string, mixed> */
    public array $offensePreview = [];

    // Incident details
    public ?int $offenseId = null;

    public string $incidentDescription = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $evidenceFile = null;

    public bool $certification = false;

    /** @var \Illuminate\Database\Eloquent\Collection<int, OffenseRule> */
    public $majorOffenses;

    /** @var list<string> */
    public array $colleges = [
        'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY',
        'COLLEGE OF CRIMINAL JUSTICE EDUCATION',
        'COLLEGE OF FISHERIES AND AQUATIC SCIENCES',
        'COLLEGE OF HOSPITALITY MANAGEMENT',
        'COLLEGE OF INDUSTRIAL TECHNOLOGY',
        'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
        'COLLEGE OF TEACHER EDUCATION',
    ];

    /**
     * Load major offenses on component initialization.
     */
    public function mount(): void
    {
        $this->majorOffenses = OffenseRule::requiringTribunalReview()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    /**
     * Auto-populate student fields when the student ID input changes.
     */
    public function updatedStudentIdInput(): void
    {
        $this->studentVerified = false;
        $this->studentDbId = null;
        $this->studentName = '';
        $this->college = '';
        $this->yearLevel = '';
        $this->section = '';
        $this->email = '';
        $this->offenseId = null;
        $this->offensePreview = [];
        $this->offenseCount = 1;
        $this->nextSanction = null;

        if (empty(trim($this->studentIdInput))) {
            return;
        }

        $student = User::where('student_id', trim($this->studentIdInput))
            ->role('student')
            ->first();

        if ($student) {
            $this->studentDbId = $student->id;
            $this->studentName = $student->name;
            $this->college = $student->college ?? '';
            $this->yearLevel = $student->year_level ?? '';
            $this->section = $student->section ?? '';
            $this->email = $student->email;

            $this->studentVerified = true;
        }
    }

    public function updatedOffenseId(): void
    {
        $this->refreshOffenseProgression();
    }

    private function refreshOffenseProgression(): void
    {
        $this->offensePreview = [];
        $this->offenseCount = 1;
        $this->nextSanction = null;

        if (! $this->studentVerified || ! $this->studentDbId || ! $this->offenseId) {
            return;
        }

        $offense = OffenseRule::query()
            ->active()
            ->requiringTribunalReview()
            ->find($this->offenseId);

        if ($offense === null) {
            return;
        }

        $preview = app(OffenseProgressionService::class)->preview($this->studentDbId, $offense);
        $this->offenseCount = $preview['next_count'];
        $this->nextSanction = $preview['next_sanction'];
        $this->offensePreview = $preview;
    }

    /**
     * Submit the formal charge.
     */
    public function submitCharge(): void
    {
        $this->validate([
            'studentIdInput' => 'required|string',
            'studentDbId' => 'required|integer|exists:users,id',
            'college' => 'required|string',
            'yearLevel' => 'required|string',
            'section' => 'required|string',
            'email' => 'required|email',
            'offenseId' => 'required|integer|exists:offense_rules,id',
            'incidentDescription' => 'required|string|min:20',
            'evidenceFile' => 'required|file|mimes:jpg,jpeg,png,pdf,mp4|max:10240',
            'certification' => 'accepted',
        ], [
            'studentDbId.required' => 'Please enter a valid Student ID and verify the student.',
            'studentDbId.exists' => 'The student ID entered was not found in the system.',
            'college.required' => 'College is required. Ensure a valid student ID was entered.',
            'yearLevel.required' => 'Year level is required.',
            'section.required' => 'Section is required.',
            'email.required' => 'Email is required.',
            'offenseId.required' => 'Please select the specific violation.',
            'incidentDescription.required' => 'A detailed description of the incident is required.',
            'incidentDescription.min' => 'Description must be at least 20 characters.',
            'evidenceFile.required' => 'Evidence attachment is mandatory for formal charges.',
            'evidenceFile.mimes' => 'Evidence must be a JPG, PNG, PDF, or MP4 file.',
            'evidenceFile.max' => 'Evidence file must not exceed 10MB.',
            'certification.accepted' => 'You must certify this report before submitting.',
        ]);

        OffenseRule::query()
            ->active()
            ->requiringTribunalReview()
            ->findOrFail($this->offenseId);

        // Store evidence outside public web root
        $evidencePath = $this->evidenceFile->store('confidential_evidence');

        $report = IncidentReport::create([
            'tracking_number' => 'INC-'.date('Y').'-'.strtoupper(Str::random(5)),
            'reporter_id' => Auth::id(),
            'student_id' => $this->studentDbId,
            'offense_id' => $this->offenseId,
            'report_type' => 'Formal Charge',
            'description' => $this->incidentDescription,
            'evidence_path' => $evidencePath,
            'status' => 'Submitted',
        ]);

        Event::dispatch(new IncidentReported($report));

        session()->flash('success', 'Formal Charge filed successfully. Tracking Number: '.$report->tracking_number);

        $this->redirect(route('staff.dashboard'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.staff.formal-charge-form');
    }
}
