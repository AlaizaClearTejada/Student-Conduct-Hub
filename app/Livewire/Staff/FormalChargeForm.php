<?php

namespace App\Livewire\Staff;

use App\Events\IncidentReported;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
use App\Services\ComplaintLetterOcrService;
use App\Services\OffenseProgressionService;
use App\Services\OffenseRuleSuggestionService;
use App\Services\TribunalCaseRegistrar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
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

    public string $program = '';

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

    public string $complainantName = '';

    public string $violationSearch = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $complaintLetter = null;

    public string $ocrExtractedText = '';

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

        $intake = session()->pull('smart_incident_intake', []);
        $studentId = is_array($intake) ? ($intake['student_id'] ?? null) : null;
        $complainant = is_array($intake) ? ($intake['complainant'] ?? null) : null;
        if (is_string($complainant)) {
            $this->complainantName = mb_substr($complainant, 0, 255);
        }

        if (is_string($studentId) && preg_match('/^\d{2}-\d{5}$/', $studentId)) {
            $this->studentIdInput = $studentId;
            $this->updatedStudentIdInput();
        }

        $description = is_array($intake) ? ($intake['description'] ?? null) : null;
        if (is_string($description)) {
            $this->incidentDescription = mb_substr($description, 0, 5000);
        }

        $offenseId = request()->integer('offense');

        if ($offenseId > 0) {
            $offense = OffenseRule::query()->active()->requiringTribunalReview()->find($offenseId);
            $this->offenseId = $offense?->id;
            $this->refreshOffenseProgression();
        }
    }

    public function updatedComplaintLetter(): void
    {
        $this->validate([
            'complaintLetter' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        try {
            $filePath = $this->complaintLetter->getRealPath() ?: $this->complaintLetter->getPathname();
            $this->ocrExtractedText = app(ComplaintLetterOcrService::class)->extractText($filePath);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'complaintLetter' => 'Complaint letter OCR failed: '.$exception->getMessage(),
            ]);
        }

        $this->incidentDescription = mb_substr($this->ocrExtractedText, 0, 5000);
        $ocrService = app(ComplaintLetterOcrService::class);
        $studentId = $ocrService->extractRespondentStudentId($this->ocrExtractedText);
        $complainant = $ocrService->extractComplainantName($this->ocrExtractedText);

        if ($studentId !== null) {
            $this->studentIdInput = $studentId;
            $this->updatedStudentIdInput();
            $this->dispatch('prefill-student-lookup', studentId: $studentId);
        }

        if ($complainant !== null) {
            $this->complainantName = $complainant;
        }

        $this->violationSearch = $this->ocrExtractedText;
        $this->suggestMatchingOffense();
    }

    public function updatedViolationSearch(): void
    {
        $this->suggestMatchingOffense();
    }

    public function selectOffenseSuggestion(int $offenseId): void
    {
        $offense = OffenseRule::query()
            ->active()
            ->requiringTribunalReview()
            ->findOrFail($offenseId);

        $this->offenseId = $offense->id;
        $this->refreshOffenseProgression();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, OffenseRule>
     */
    public function getOffenseSuggestionsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return app(OffenseRuleSuggestionService::class)->suggest($this->violationSearch, majorOnly: true);
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
        $this->program = '';
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
            $this->program = $student->program ?? '';
            $this->yearLevel = $student->year_level ?? '';
            $this->section = $student->section ?? '';
            $this->email = $student->email;

            $this->studentVerified = true;
        }
    }

    #[On('staff-student-selected')]
    public function useSelectedStudent(int $userId): void
    {
        $student = User::query()->role('student')->findOrFail($userId);
        $studentId = (string) $student->student_id;

        if ($this->studentIdInput !== $studentId || ! $this->studentVerified) {
            $this->studentIdInput = $studentId;
            $this->updatedStudentIdInput();

            return;
        }

        $this->refreshOffenseProgression();
    }

    #[On('staff-student-lookup-cleared')]
    public function clearSelectedStudent(): void
    {
        $this->studentIdInput = '';
        $this->updatedStudentIdInput();
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
            'studentIdInput' => ['required', 'string', 'regex:/^\d{2}-\d{5}$/'],
            'studentDbId' => 'required|integer|exists:users,id',
            'college' => 'required|string',
            'yearLevel' => 'required|string',
            'section' => 'required|string',
            'email' => 'required|email',
            'offenseId' => 'required|integer|exists:offense_rules,id',
            'incidentDescription' => 'required|string|min:20',
            'complainantName' => ['nullable', 'string', 'max:255'],
            'evidenceFile' => 'required|file|mimes:jpg,jpeg,png,pdf,mp4|max:10240',
            'certification' => 'accepted',
        ], [
            'studentDbId.required' => 'Please enter a valid Student ID and verify the student.',
            'studentIdInput.regex' => 'Student ID must follow the format 24-00005.',
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
            'complainant_name' => $this->complainantName !== '' ? $this->complainantName : null,
            'description' => $this->incidentDescription,
            'evidence_path' => $evidencePath,
            'status' => 'Submitted',
        ]);

        Event::dispatch(new IncidentReported($report));
        app(TribunalCaseRegistrar::class)->registerEligibleReport($report);

        session()->flash('success', 'Formal Charge filed successfully. Tracking Number: '.$report->tracking_number);

        $this->redirect(route('staff.dashboard'), navigate: true);
    }

    private function suggestMatchingOffense(): void
    {
        $offense = $this->getOffenseSuggestionsProperty()->first();

        if ($offense === null) {
            return;
        }

        $this->offenseId = $offense->id;
        $this->refreshOffenseProgression();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.staff.formal-charge-form');
    }
}
