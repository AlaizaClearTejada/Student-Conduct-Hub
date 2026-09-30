<?php

namespace App\Livewire\Admin;

use App\Exports\ByTypeReportExport;
use App\Exports\CaseSummaryExport;
use App\Exports\MonthlyReportExport;
use App\Mail\StudentChargeNoticeMail;
use App\Models\CaseEvidence;
use App\Models\CaseWorkflowLog;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\TribunalCase;
use App\Models\User;
use App\Models\ViolationRecord;
use App\Services\TribunalCaseRouter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class CaseManagement extends Component
{
    use WithPagination;

    // Tab
    public string $activeTab = 'cases';

    // Filters
    public $filterStatus = '';

    public $filterInvestigationType = '';

    public $filterOffenseCategory = '';

    public $filterOverdueOnly = false;

    public $searchTerm = '';

    // Sorting
    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    // Modal States
    public $selectedCase;

    public $sourceReport;

    public $showAssignSDTModal = false;

    public $showResolveCaseModal = false;

    public $sdtMembers = [];

    public $selectedSDTMembers = [];

    // Inbox / Accept Modal
    public $showAcceptModal = false;

    public ?int $acceptCaseId = null;

    public string $acceptInvestigationType = 'Tribunal';

    public string $acceptDateOfIncident = '';

    // Resolve Case Fields
    public $settledBy = '';

    public $sanctionImposedDate = '';

    public $sanctionImposedTime = '';

    public $sanctionEffectiveDate = '';

    public $sanctionEffectiveTime = '';

    public $actionTaken = '';

    public $appliedSanction = '';

    public $sanctionCaseContext = [];

    // Send Notice Modal
    public $showSendNoticeModal = false;

    // Schedule Conference Modal
    public $showScheduleConferenceModal = false;

    public string $conferenceDate = '';

    public string $conferenceTime = '';

    public string $conferenceNotes = '';

    // Report Filters
    public string $reportPeriodType = 'monthly';

    public string $reportMonth = '';

    public string $reportQuarter = '';

    public string $reportSemester = '';

    public string $reportYear = '';

    public string $reportStartDate = '';

    public string $reportEndDate = '';

    protected $queryString = [
        'activeTab' => ['except' => 'inbox'],
        'filterStatus' => ['except' => ''],
        'filterInvestigationType' => ['except' => ''],
        'searchTerm' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->activeTab = 'inbox';
        $this->reportYear = (string) now()->year;
        $this->reportMonth = now()->format('m');
        $this->reportQuarter = (string) ceil(now()->month / 3);
        $this->reportSemester = now()->month <= 5 ? '2nd' : (now()->month <= 10 ? '1st' : 'summer');
        $this->loadSDTMembers();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function loadSDTMembers(): void
    {
        $this->sdtMembers = User::role(['staff', 'administrator'])
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->getRoleNames()->first(),
            ]);
    }

    public function updatingSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['filterStatus', 'filterInvestigationType', 'filterOffenseCategory', 'filterOverdueOnly', 'searchTerm']);
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function viewCase($caseId): void
    {
        $case = ViolationRecord::with(['student', 'offenseRule', 'reporter', 'evidence', 'workflowLogs.actor'])
            ->findOrFail($caseId);

        $case->logAccess(Auth::id(), 'OSDW Case Review');

        $this->selectedCase = $case;

        // Find source IncidentReport if case was opened from one (for evidence fallback)
        $this->sourceReport = null;
        $sourceLog = $case->workflowLogs->first(fn ($log) => $log->action_type === 'Case Created' && isset($log->metadata['source_report']));
        if ($sourceLog) {
            $this->sourceReport = IncidentReport::where('tracking_number', $sourceLog->metadata['source_report'])->first();
        }

        $this->dispatch('case-loaded');
    }

    public function closeCase(): void
    {
        $this->selectedCase = null;
        $this->sourceReport = null;
    }

    public function openAssignSDTModal($caseId): void
    {
        $this->selectedCase = ViolationRecord::findOrFail($caseId);
        $this->selectedSDTMembers = $this->selectedCase->sdt_members ?? [];
        $this->showAssignSDTModal = true;
    }

    public function assignSDT(): void
    {
        $this->validate([
            'selectedSDTMembers' => 'required|array|min:5|max:5',
        ], [
            'selectedSDTMembers.min' => 'You must select exactly 5 tribunal members per CSU Section G.1',
            'selectedSDTMembers.max' => 'You must select exactly 5 tribunal members per CSU Section G.1',
        ]);

        $this->selectedCase->update([
            'assigned_to_sdt' => true,
            'sdt_members' => $this->selectedSDTMembers,
            'status' => 'Pending Review',
            'investigation_type' => 'Tribunal',
        ]);

        app(TribunalCaseRouter::class)->routeViolationRecord($this->selectedCase);

        CaseWorkflowLog::logAction(
            $this->selectedCase->id,
            Auth::id(),
            'Hearing Scheduled',
            'Student Disciplinary Tribunal assigned (5 members)',
            ['sdt_member_ids' => $this->selectedSDTMembers]
        );

        session()->flash('success', 'Student Disciplinary Tribunal successfully assigned to Case #'.$this->selectedCase->case_tracking_number);

        $this->showAssignSDTModal = false;
        $this->reset('selectedSDTMembers');
    }

    public function scheduleHearing($caseId, $hearingDate): void
    {
        $case = ViolationRecord::findOrFail($caseId);

        $case->update([
            'hearing_scheduled_date' => $hearingDate,
            'decision_deadline' => Carbon::parse($hearingDate)->addWeekdays(15),
        ]);

        CaseWorkflowLog::logAction(
            $case->id,
            Auth::id(),
            'Hearing Scheduled',
            "Hearing scheduled for {$hearingDate}",
            ['hearing_date' => $hearingDate]
        );

        session()->flash('success', 'Hearing scheduled for Case #'.$case->case_tracking_number);
    }

    public function openResolveCaseModal($caseId): void
    {
        $this->selectedCase = ViolationRecord::with(['student', 'offenseRule', 'reporter'])->findOrFail($caseId);
        $this->reset(['settledBy', 'sanctionEffectiveDate', 'sanctionEffectiveTime', 'actionTaken']);

        // Pre-fill sanction date/time with current
        $this->sanctionImposedDate = now()->toDateString();
        $this->sanctionImposedTime = now()->format('H:i');

        // Pre-fill applied sanction from offense rule's progressive sanction
        $offense = $this->selectedCase->offenseRule;
        $offenseCount = $this->selectedCase->offense_count ?? 1;
        $this->appliedSanction = $this->selectedCase->applied_sanction
            ?: ($offense?->getSanctionForOffenseCount($offenseCount) ?? $offense?->standard_sanction ?? '');

        // Build context for the modal header
        $this->sanctionCaseContext = [
            'case_number' => $this->selectedCase->case_tracking_number,
            'student_name' => $this->selectedCase->student?->name ?? 'N/A',
            'student_id' => $this->selectedCase->student?->student_id ?? 'N/A',
            'college' => $this->selectedCase->student?->college ?? 'N/A',
            'offense_title' => $offense?->title ?? 'N/A',
            'offense_code' => $offense?->code ?? '',
            'offense_category' => $offense?->category ?? 'N/A',
            'gravity' => $offense?->gravity ?? 'N/A',
            'offense_count' => $offenseCount,
            'investigation_type' => $this->selectedCase->investigation_type ?? 'N/A',
            'date_of_incident' => $this->selectedCase->date_of_incident?->format('F d, Y') ?? 'N/A',
            'charge_filed' => $this->selectedCase->charge_filed_date?->format('F d, Y') ?? 'N/A',
            'first_sanction' => $offense?->first_offense_sanction ?? null,
            'second_sanction' => $offense?->second_offense_sanction ?? null,
            'third_sanction' => $offense?->third_offense_sanction ?? null,
        ];

        // Pre-set settled_by based on investigation type
        if ($this->selectedCase->investigation_type === 'Dean Direct') {
            $this->settledBy = 'Dean';
        }

        $this->showResolveCaseModal = true;
    }

    public function resolveCase(): void
    {
        $this->validate([
            'settledBy' => 'required|in:Dean,OSDW',
            'sanctionImposedDate' => 'required|date',
            'sanctionImposedTime' => 'required',
            'appliedSanction' => 'required|string|min:2',
            'actionTaken' => 'required|string|min:5',
            'sanctionEffectiveDate' => 'nullable|date',
            'sanctionEffectiveTime' => 'nullable',
        ], [
            'appliedSanction.required' => 'Please specify the sanction to be applied.',
        ]);

        $sanctionImposedAt = Carbon::parse($this->sanctionImposedDate.' '.$this->sanctionImposedTime);
        $sanctionEffectiveAt = $this->sanctionEffectiveDate
            ? Carbon::parse($this->sanctionEffectiveDate.' '.($this->sanctionEffectiveTime ?: '00:00'))
            : null;

        $this->selectedCase->update([
            'applied_sanction' => $this->appliedSanction,
            'sanction_imposed_at' => $sanctionImposedAt,
            'sanction_effective_at' => $sanctionEffectiveAt,
            'settled_by' => $this->settledBy,
            'action_taken' => $this->actionTaken,
            'resolution_date' => now(),
            'status' => 'Sanction Active',
            'decided_by' => Auth::id(),
        ]);

        CaseWorkflowLog::logAction(
            $this->selectedCase->id,
            Auth::id(),
            'Sanction Applied',
            "Case settled by {$this->settledBy}. Sanction: {$this->appliedSanction}. Action: {$this->actionTaken}",
            [
                'settled_by' => $this->settledBy,
                'applied_sanction' => $this->appliedSanction,
                'sanction_imposed_at' => $sanctionImposedAt->toISOString(),
                'sanction_effective_at' => $sanctionEffectiveAt?->toISOString(),
                'action_taken' => $this->actionTaken,
            ]
        );

        session()->flash('success', 'Sanction applied to Case #'.$this->selectedCase->case_tracking_number);

        $this->showResolveCaseModal = false;
        $this->selectedCase = null;
        $this->sanctionCaseContext = [];
    }

    /**
     * Open the Accept modal for an incoming IncidentReport.
     */
    public function openAcceptModal(int $reportId): void
    {
        $report = IncidentReport::with('offense')->findOrFail($reportId);
        $this->acceptCaseId = $reportId;
        $this->acceptInvestigationType = ($report->report_type === 'Formal Charge' || $report->offense?->requiresTribunalReview())
            ? 'Tribunal'
            : 'Summary';
        $this->acceptDateOfIncident = $report->created_at->toDateString();
        $this->showAcceptModal = true;
    }

    /**
     * Accept an incident report and create a formal ViolationRecord.
     */
    public function acceptReport(): void
    {
        $this->validate([
            'acceptInvestigationType' => 'required|in:Tribunal,Summary,Dean Direct',
            'acceptDateOfIncident' => 'required|date|before_or_equal:today',
        ], [
            'acceptDateOfIncident.before_or_equal' => 'Date of incident cannot be in the future.',
        ]);

        $report = IncidentReport::with('offense')->findOrFail($this->acceptCaseId);
        $offense = $report->offense;
        $isTribunalCase = $this->acceptInvestigationType === 'Tribunal' || $offense?->requiresTribunalReview();
        $investigationType = $isTribunalCase ? 'Tribunal' : $this->acceptInvestigationType;
        $tribunalCase = TribunalCase::where('incident_report_id', $report->id)->first();

        // Count prior offenses to determine progressive sanction
        $offenseCount = ViolationRecord::where('student_id', $report->student_id)
            ->where('offense_id', $report->offense_id)
            ->count() + 1;

        $appliedSanction = $offense?->getSanctionForOffenseCount($offenseCount)
            ?? $offense?->standard_sanction
            ?? '';

        $record = ViolationRecord::create([
            'case_tracking_number' => $tribunalCase?->case_number ?? ViolationRecord::generateCaseTrackingNumber(),
            'student_id' => $report->student_id,
            'offense_id' => $report->offense_id,
            'offense_count' => $offenseCount,
            'applied_sanction' => $appliedSanction,
            'reported_by' => $report->reporter_id,
            'status' => 'Pending Review',
            'investigation_type' => $investigationType,
            'incident_description' => $report->description,
            'date_of_incident' => $this->acceptDateOfIncident,
            'charge_filed_date' => now()->toDateString(),
            'answer_deadline' => now()->addWeekdays(5)->toDateString(),
        ]);

        // Transfer evidence from IncidentReport to CaseEvidence
        $tribunalDocumentPath = $report->evidence_path;

        if ($report->evidence_path && Storage::disk('local')->exists($report->evidence_path)) {
            $fileName = basename($report->evidence_path);
            $mime = Storage::disk('local')->mimeType($report->evidence_path);
            $size = Storage::disk('local')->size($report->evidence_path);
            $fileType = str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');
            $evidenceType = str_starts_with($mime, 'image/') ? 'Photo Evidence' : ((str_contains($mime, 'pdf') || str_contains($mime, 'word')) ? 'Document' : 'Other');

            // Copy to case-evidence directory
            $newPath = 'case-evidence/'.$record->case_tracking_number.'/'.$fileName;
            Storage::disk('local')->put($newPath, Storage::disk('local')->get($report->evidence_path));
            $tribunalDocumentPath = $newPath;

            CaseEvidence::create([
                'violation_record_id' => $record->id,
                'uploaded_by' => $report->reporter_id,
                'file_name' => $fileName,
                'file_path' => $newPath,
                'file_type' => $fileType,
                'mime_type' => $mime,
                'file_size' => $size,
                'description' => 'Evidence transferred from incident report '.$report->tracking_number,
                'evidence_type' => $evidenceType,
            ]);
        }

        if ($isTribunalCase) {
            app(TribunalCaseRouter::class)->routeViolationRecord($record, $report, $tribunalDocumentPath);
        }

        CaseWorkflowLog::logAction(
            $record->id,
            Auth::id(),
            'Case Created',
            "Case opened from incident report {$report->tracking_number} ({$report->report_type})",
            ['source_report' => $report->tracking_number, 'report_type' => $report->report_type]
        );

        $report->update(['status' => 'Under Review by OSDW']);

        session()->flash('success', "Case #{$record->case_tracking_number} opened successfully from report {$report->tracking_number}.");

        $this->showAcceptModal = false;
        $this->acceptCaseId = null;
        $this->activeTab = 'cases';
    }

    /**
     * Dismiss an incoming IncidentReport.
     */
    public function dismissReport(int $reportId): void
    {
        $report = IncidentReport::findOrFail($reportId);
        $report->update(['status' => 'Dismissed']);
        session()->flash('success', "Report {$report->tracking_number} has been dismissed.");
    }

    /**
     * Open the Send Notice confirmation modal.
     */
    public function openSendNoticeModal(int $caseId): void
    {
        $this->selectedCase = ViolationRecord::with('student', 'offenseRule')->findOrFail($caseId);
        $this->showSendNoticeModal = true;
    }

    /**
     * Send formal notice of charge to the student via email.
     */
    public function sendNoticeToStudent(): void
    {
        $case = $this->selectedCase;

        if (! $case->canSendNotice()) {
            session()->flash('success', 'Maximum number of notices (3) have already been sent for this case.');
            $this->showSendNoticeModal = false;

            return;
        }

        $student = $case->student;
        $case->load('offenseRule');

        // Send the email notification
        Mail::to($student->email)->send(new StudentChargeNoticeMail($case, $student));

        $newCount = ($case->notice_count ?? 0) + 1;
        $ordinal = $newCount === 1 ? '1st' : ($newCount === 2 ? '2nd' : '3rd');

        // Update the case
        $case->update([
            'status' => 'Notice Sent',
            'notice_count' => $newCount,
            'notice_sent_at' => now(),
            'notice_sent_by' => Auth::id(),
            'answer_deadline' => now()->addWeekdays(5),
        ]);

        CaseWorkflowLog::logAction(
            $case->id,
            Auth::id(),
            'Charge Filed',
            "{$ordinal} formal notice of charge sent to student ({$student->email}). Answer deadline: ".$case->answer_deadline->format('M d, Y'),
            ['student_email' => $student->email, 'answer_deadline' => $case->answer_deadline->toDateString(), 'notice_count' => $newCount]
        );

        session()->flash('success', "{$ordinal} formal notice sent to {$student->name} for Case #{$case->case_tracking_number}. Answer deadline: {$case->answer_deadline->format('M d, Y')}.");

        $this->showSendNoticeModal = false;
    }

    /**
     * Advance a case from 'Notice Sent' to 'Under Investigation'.
     */
    public function advanceToInvestigation(int $caseId): void
    {
        $case = ViolationRecord::findOrFail($caseId);

        if ($case->status !== 'Notice Sent') {
            return;
        }

        // Allow if student answered or deadline passed
        if (! $case->student_answer_submitted_date && ! $case->isAnswerOverdue()) {
            session()->flash('success', 'Cannot advance: student has not responded and the deadline has not passed.');

            return;
        }

        $reason = $case->student_answer_submitted_date
            ? 'Student answer received; case moved to investigation.'
            : 'Answer deadline expired (ex parte per CSU G.9); case moved to investigation.';

        $case->update(['status' => 'Under Investigation']);

        CaseWorkflowLog::logAction($case->id, Auth::id(), 'Status Changed', $reason);

        session()->flash('success', "Case #{$case->case_tracking_number} moved to Under Investigation.");
    }

    /**
     * Open the Schedule Conference modal.
     */
    public function openScheduleConferenceModal(int $caseId): void
    {
        $this->selectedCase = ViolationRecord::findOrFail($caseId);
        $this->reset(['conferenceDate', 'conferenceTime', 'conferenceNotes']);
        $this->showScheduleConferenceModal = true;
    }

    /**
     * Schedule a conference/review for the case.
     */
    public function scheduleConference(): void
    {
        $this->validate([
            'conferenceDate' => 'required|date|after_or_equal:today',
            'conferenceTime' => 'required',
            'conferenceNotes' => 'nullable|string|max:2000',
        ]);

        $conferenceDateTime = Carbon::parse($this->conferenceDate.' '.$this->conferenceTime);

        $this->selectedCase->update([
            'conference_date' => $conferenceDateTime,
            'conference_notes' => $this->conferenceNotes ?: null,
        ]);

        CaseWorkflowLog::logAction(
            $this->selectedCase->id,
            Auth::id(),
            'Hearing Scheduled',
            'Conference/review scheduled for '.$conferenceDateTime->format('F d, Y \a\t g:i A'),
            ['conference_date' => $conferenceDateTime->toISOString()]
        );

        session()->flash('success', 'Conference scheduled for Case #'.$this->selectedCase->case_tracking_number);

        $this->showScheduleConferenceModal = false;
    }

    /**
     * Mark a conference as held/conducted.
     */
    public function markConferenceHeld(int $caseId): void
    {
        $case = ViolationRecord::findOrFail($caseId);
        $case->update(['conference_held_at' => now()]);

        CaseWorkflowLog::logAction(
            $case->id,
            Auth::id(),
            'Hearing Held',
            'Conference/review conducted by OSDW'
        );

        session()->flash('success', 'Conference marked as conducted for Case #'.$case->case_tracking_number);
    }

    /**
     * Get all IncidentReports for the inbox (submitted + under review).
     */
    public function getIncomingReportsProperty()
    {
        return IncidentReport::with(['student', 'offense', 'reporter'])
            ->whereIn('status', ['Submitted', 'Under Review by OSDW', 'Dismissed'])
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'inbox_page');
    }

    /**
     * Count of unprocessed (Submitted) reports for the badge.
     */
    public function getInboxCountProperty(): int
    {
        return IncidentReport::where('status', 'Submitted')->count();
    }

    /**
     * Get the date range based on the report period selector.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function getReportDateRange(): array
    {
        $year = (int) ($this->reportYear ?: now()->year);

        return match ($this->reportPeriodType) {
            'monthly' => [
                Carbon::create($year, (int) $this->reportMonth, 1)->startOfMonth(),
                Carbon::create($year, (int) $this->reportMonth, 1)->endOfMonth(),
            ],
            'quarterly' => [
                Carbon::create($year, (((int) $this->reportQuarter - 1) * 3) + 1, 1)->startOfMonth(),
                Carbon::create($year, ((int) $this->reportQuarter) * 3, 1)->endOfMonth(),
            ],
            'semestral' => match ($this->reportSemester) {
                '1st' => [Carbon::create($year, 6, 1)->startOfMonth(), Carbon::create($year, 10, 31)->endOfDay()],
                '2nd' => [Carbon::create($year, 11, 1)->startOfMonth(), Carbon::create($year + 1, 3, 31)->endOfDay()],
                'summer' => [Carbon::create($year, 4, 1)->startOfMonth(), Carbon::create($year, 5, 31)->endOfDay()],
                default => [Carbon::create($year, 1, 1)->startOfYear(), Carbon::create($year, 12, 31)->endOfYear()],
            },
            'custom' => [
                $this->reportStartDate ? Carbon::parse($this->reportStartDate)->startOfDay() : now()->startOfYear(),
                $this->reportEndDate ? Carbon::parse($this->reportEndDate)->endOfDay() : now()->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    /**
     * Get a human-readable period label.
     */
    public function getReportPeriodLabel(): string
    {
        [$start, $end] = $this->getReportDateRange();

        return match ($this->reportPeriodType) {
            'monthly' => $start->format('F Y'),
            'quarterly' => 'Q'.$this->reportQuarter.' '.$this->reportYear,
            'semestral' => ucfirst($this->reportSemester).' Semester '.($this->reportSemester === '2nd' ? $this->reportYear.'-'.($this->reportYear + 1) : $this->reportYear),
            'custom' => $start->format('M d, Y').' — '.$end->format('M d, Y'),
            default => $start->format('F Y'),
        };
    }

    public function getCaseSummaryDataProperty(): array
    {
        [$start, $end] = $this->getReportDateRange();

        $records = ViolationRecord::with(['student', 'offenseRule', 'reporter', 'decisionMaker'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'records' => $records,
            'period' => $this->getReportPeriodLabel(),
            'start' => $start,
            'end' => $end,
            'hasRecords' => $records->isNotEmpty(),
        ];
    }

    public function getMonthlyReportDataProperty(): array
    {
        $year = (int) ($this->reportYear ?: now()->year);
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create($year, $m, 1)->startOfMonth();
            $end = Carbon::create($year, $m, 1)->endOfMonth();

            $records = ViolationRecord::whereBetween('created_at', [$start, $end]);

            $months[] = [
                'month' => $start->format('F'),
                'month_num' => $m,
                'total' => (clone $records)->count(),
                'pending' => (clone $records)->where('status', 'Pending Review')->count(),
                'active' => (clone $records)->where('status', 'Sanction Active')->count(),
                'resolved' => (clone $records)->where('status', 'Resolved')->count(),
                'by_dean' => (clone $records)->where('settled_by', 'Dean')->count(),
                'by_osdw' => (clone $records)->where('settled_by', 'OSDW')->count(),
            ];
        }

        $hasAnyRecords = collect($months)->sum('total') > 0;

        return [
            'months' => $months,
            'year' => $year,
            'hasRecords' => $hasAnyRecords,
        ];
    }

    public function getByTypeReportDataProperty(): array
    {
        [$start, $end] = $this->getReportDateRange();

        $categories = OffenseRule::select('category')->distinct()->pluck('category');
        $data = [];

        foreach ($categories as $category) {
            $records = ViolationRecord::whereBetween('created_at', [$start, $end])
                ->whereHas('offenseRule', fn ($q) => $q->where('category', $category));

            $data[] = [
                'category' => $category,
                'total' => (clone $records)->count(),
                'pending' => (clone $records)->where('status', 'Pending Review')->count(),
                'active' => (clone $records)->where('status', 'Sanction Active')->count(),
                'resolved' => (clone $records)->where('status', 'Resolved')->count(),
                'by_dean' => (clone $records)->where('settled_by', 'Dean')->count(),
                'by_osdw' => (clone $records)->where('settled_by', 'OSDW')->count(),
            ];
        }

        $hasRecords = collect($data)->sum('total') > 0;

        return [
            'categories' => $data,
            'period' => $this->getReportPeriodLabel(),
            'hasRecords' => $hasRecords,
        ];
    }

    public function exportCaseSummary()
    {
        [$start, $end] = $this->getReportDateRange();

        return Excel::download(
            new CaseSummaryExport($start, $end),
            'case-summary-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function exportMonthlyReport()
    {
        $year = (int) ($this->reportYear ?: now()->year);

        return Excel::download(
            new MonthlyReportExport($year),
            'monthly-report-'.$year.'.xlsx'
        );
    }

    public function exportByTypeReport()
    {
        [$start, $end] = $this->getReportDateRange();

        return Excel::download(
            new ByTypeReportExport($start, $end),
            'by-type-report-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function getCasesProperty()
    {
        $query = ViolationRecord::with(['student', 'offenseRule', 'reporter'])
            ->when($this->searchTerm, function ($q) {
                $q->where('case_tracking_number', 'like', "%{$this->searchTerm}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$this->searchTerm}%"))
                    ->orWhereHas('offenseRule', fn ($oq) => $oq->where('title', 'like', "%{$this->searchTerm}%"));
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterInvestigationType, fn ($q) => $q->where('investigation_type', $this->filterInvestigationType))
            ->when($this->filterOffenseCategory, function ($q) {
                $q->whereHas('offenseRule', fn ($oq) => $oq->where('category', $this->filterOffenseCategory));
            })
            ->when($this->filterOverdueOnly, function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNotNull('answer_deadline')
                        ->where('answer_deadline', '<', now())
                        ->whereNull('student_answer_submitted_date')
                        ->orWhere(function ($dsq) {
                            $dsq->whereNotNull('decision_deadline')
                                ->where('decision_deadline', '<', now())
                                ->whereNull('final_decision');
                        });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(20);
    }

    public function getStatisticsProperty()
    {
        return [
            'total_cases' => ViolationRecord::count(),
            'pending_review' => ViolationRecord::where('status', 'Pending Review')->count(),
            'notice_sent' => ViolationRecord::where('status', 'Notice Sent')->count(),
            'under_investigation' => ViolationRecord::where('status', 'Under Investigation')->count(),
            'active_sanctions' => ViolationRecord::where('status', 'Sanction Active')->count(),
            'resolved' => ViolationRecord::where('status', 'Resolved')->count(),
            'overdue_answer' => ViolationRecord::whereNotNull('answer_deadline')
                ->where('answer_deadline', '<', now())
                ->whereNull('student_answer_submitted_date')
                ->count(),
            'overdue_decision' => ViolationRecord::whereNotNull('decision_deadline')
                ->where('decision_deadline', '<', now())
                ->whereNull('final_decision')
                ->count(),
            'awaiting_tribunal' => ViolationRecord::where('investigation_type', 'Tribunal')
                ->where('assigned_to_sdt', false)
                ->count(),
        ];
    }

    public function render()
    {
        $viewData = [
            'cases' => $this->cases,
            'statistics' => $this->statistics,
            'offenseCategories' => OffenseRule::select('category')->distinct()->pluck('category'),
            'inboxCount' => $this->inboxCount,
        ];

        if ($this->activeTab === 'inbox' || $this->showAcceptModal) {
            $viewData['incomingReports'] = $this->incomingReports;
            $viewData['acceptReport'] = $this->acceptCaseId
                ? IncidentReport::with(['student', 'offense', 'reporter'])->find($this->acceptCaseId)
                : null;
        }

        if ($this->activeTab === 'case-summary') {
            $viewData['caseSummary'] = $this->caseSummaryData;
        } elseif ($this->activeTab === 'monthly-report') {
            $viewData['monthlyReport'] = $this->monthlyReportData;
        } elseif ($this->activeTab === 'by-type-report') {
            $viewData['byTypeReport'] = $this->byTypeReportData;
        }

        return view('livewire.admin.case-management', $viewData)
            ->layout('layouts.admin', ['title' => 'Case Management']);
    }
}
