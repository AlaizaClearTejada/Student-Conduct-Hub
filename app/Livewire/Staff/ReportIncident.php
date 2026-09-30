<?php

namespace App\Livewire\Staff;

use App\Events\IncidentReported;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReportIncident extends Component
{
    use WithFileUploads;

    // Form Fields
    public $student_id;

    public $student_id_search;

    public $offense_category = '';

    public $offense_id = '';

    public $date_of_incident;

    public $incident_description = '';

    public $evidence_files = [];

    public $evidence_descriptions = [];

    // Computed Data
    public $selectedStudent;

    public $offenseCategories = [];

    public $filteredOffenses = [];

    public $selectedOffense;

    protected $rules = [
        'student_id' => 'required|exists:users,id',
        'offense_id' => 'required|exists:offense_rules,id',
        'date_of_incident' => 'required|date|before_or_equal:today',
        'incident_description' => 'required|string|min:20|max:5000',
        'evidence_files.*' => 'nullable|file|max:10240', // Max 10MB per file
    ];

    protected $messages = [
        'incident_description.min' => 'Please provide a detailed incident description (at least 20 characters).',
        'date_of_incident.before_or_equal' => 'Incident date cannot be in the future.',
    ];

    public function mount(): void
    {
        $this->date_of_incident = now()->format('Y-m-d');
        $this->loadOffenseCategories();
    }

    public function loadOffenseCategories(): void
    {
        $this->offenseCategories = OffenseRule::active()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    public function updatedOffenseCategory(): void
    {
        $this->offense_id = '';
        $this->selectedOffense = null;

        if ($this->offense_category) {
            $this->filteredOffenses = OffenseRule::active()
                ->where('category', $this->offense_category)
                ->orderBy('title')
                ->get();
        } else {
            $this->filteredOffenses = [];
        }
    }

    public function updatedOffenseId(): void
    {
        if ($this->offense_id) {
            $this->selectedOffense = OffenseRule::find($this->offense_id);
        } else {
            $this->selectedOffense = null;
        }
    }

    public function searchStudent(): void
    {
        $this->validate([
            'student_id_search' => 'required|string',
        ]);

        $student = User::role('Student')
            ->where(function ($query) {
                $query->where('student_id', $this->student_id_search)
                    ->orWhere('email', $this->student_id_search);
            })
            ->first();

        if ($student) {
            $this->student_id = $student->id;
            $this->selectedStudent = $student;
            $this->dispatch('student-found');
        } else {
            $this->selectedStudent = null;
            $this->addError('student_id_search', 'Student not found. Please verify the Student ID or email.');
        }
    }

    public function clearStudent(): void
    {
        $this->student_id = null;
        $this->student_id_search = '';
        $this->selectedStudent = null;
    }

    public function removeEvidence($index): void
    {
        unset($this->evidence_files[$index]);
        unset($this->evidence_descriptions[$index]);

        $this->evidence_files = array_values($this->evidence_files);
        $this->evidence_descriptions = array_values($this->evidence_descriptions);
    }

    public function submitReport(): void
    {
        $this->validate();

        // Determine report type based on offense gravity
        $offenseRule = OffenseRule::find($this->offense_id);
        $reportType = ($offenseRule && $offenseRule->gravity === 'major') ? 'Formal Charge' : 'Quick Log';

        // Store the first evidence file (IncidentReport supports a single evidence path)
        $evidencePath = null;
        if (! empty($this->evidence_files)) {
            $evidencePath = $this->evidence_files[0]->store('confidential_evidence', 'local');
        }

        // Create incident report for OSDW inbox review
        $report = IncidentReport::create([
            'tracking_number' => 'INC-'.date('Y').'-'.strtoupper(Str::random(5)),
            'reporter_id' => Auth::id(),
            'student_id' => $this->student_id,
            'offense_id' => $this->offense_id,
            'report_type' => $reportType,
            'description' => $this->incident_description,
            'evidence_path' => $evidencePath,
            'status' => 'Submitted',
        ]);

        // Notify OSDW administrators
        Event::dispatch(new IncidentReported($report));

        // Success notification
        session()->flash('success', "Incident reported successfully. Tracking #: {$report->tracking_number}. The OSDW administrator will review your report.");

        // Reset form
        $this->reset(['student_id', 'student_id_search', 'offense_category', 'offense_id',
            'incident_description', 'evidence_files', 'evidence_descriptions']);
        $this->selectedStudent = null;
        $this->selectedOffense = null;
        $this->date_of_incident = now()->format('Y-m-d');

        $this->dispatch('incident-reported');
    }

    public function render()
    {
        return view('livewire.staff.report-incident');
    }
}
