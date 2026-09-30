<?php

namespace App\Http\Controllers;

use App\Events\IncidentReported;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
use App\Models\ViolationRecord;
use App\Services\DecisionSupportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffIncidentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the staff dashboard with incident reporting hub.
     *
     * Shows recent reports submitted by the authenticated staff member.
     */
    public function index(): View
    {
        $staffId = Auth::id();

        // Get recent reports submitted by this staff member
        // Eager load relationships to prevent N+1 queries
        $recentReports = IncidentReport::with(['student', 'offense'])
            ->where('reporter_id', $staffId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Dashboard metrics
        $totalReports = IncidentReport::where('reporter_id', $staffId)->count();
        $submittedCount = IncidentReport::where('reporter_id', $staffId)
            ->where('status', 'Submitted')
            ->count();
        $underReviewCount = IncidentReport::where('reporter_id', $staffId)
            ->where('status', 'Under Review by OSDW')
            ->count();
        $resolvedCount = IncidentReport::where('reporter_id', $staffId)
            ->where('status', 'Resolved')
            ->count();

        return view('staff.dashboard', compact(
            'recentReports',
            'totalReports',
            'submittedCount',
            'underReviewCount',
            'resolvedCount'
        ));
    }

    /**
     * Show the form for creating a new incident report.
     */
    public function create(): View
    {
        // Get all students (for dropdown selection)
        $students = User::role('student')
            ->orderBy('name')
            ->get();

        // Get all offense rules (for offense selection)
        $offenseRules = OffenseRule::orderBy('code')
            ->get();

        return view('staff.report-create', compact('students', 'offenseRules'));
    }

    /**
     * Store a newly created incident report in storage.
     *
     * Validates input, securely uploads evidence, and triggers notification to OSDW.
     */
    public function store(Request $request, DecisionSupportService $decisionSupport): RedirectResponse
    {
        // 1. Strict Validation based on CSU rules
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'offense_id' => 'required|exists:offense_rules,id',
            'report_type' => 'required|in:Quick Log,Formal Charge',
            'description' => 'required|string|min:10',
            'evidence' => 'nullable|file|mimes:jpg,png,pdf|max:5120', // Max 5MB
        ]);

        // 2. Secure File Upload (Store outside public directory for privacy)
        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('confidential_evidence', 'local');
        }

        // 3. Analyze complaint with Decision Support Service
        $analysis = $decisionSupport->analyze($validated['description']);
        $topRecommendation = $analysis['top_recommendation'] ?? [];

        // 4. Create the Report
        $report = IncidentReport::create([
            'tracking_number' => 'INC-'.date('Y').'-'.strtoupper(Str::random(5)),
            'reporter_id' => Auth::id(),
            'student_id' => $validated['student_id'],
            'offense_id' => $validated['offense_id'],
            'report_type' => $validated['report_type'],
            'description' => $validated['description'],
            'evidence_path' => $evidencePath,
            'recommended_violation' => $topRecommendation['violation'] ?? null,
            'recommended_sanction' => $topRecommendation['recommendation'] ?? null,
            'severity' => $topRecommendation['severity'] ?? null,
            'decision_score' => $topRecommendation['score'] ?? 0,
            'matched_keywords' => $topRecommendation['matched_keywords'] ?? null,
        ]);

        // 5. Trigger Notification to OSDW (Admin)
        Event::dispatch(new IncidentReported($report));

        return redirect()->route('staff.dashboard')
            ->with('success', 'Incident reported successfully. Tracking Number: '.$report->tracking_number);
    }

    /**
     * Display a specific incident report.
     *
     * CRITICAL: Authorization ensures staff can only view their own reports.
     */
    public function show(IncidentReport $report): View
    {
        // Policy check: ensures authenticated user is the reporter
        $this->authorize('view', $report);

        // Eager load relationships
        $report->load(['student', 'offense', 'reporter']);

        return view('staff.report-show', compact('report'));
    }

    /**
     * Display all reports submitted by the authenticated staff member.
     */
    public function myReports(): View
    {
        $staffId = Auth::id();

        // Get all reports submitted by this staff member with pagination
        $reports = IncidentReport::with(['student', 'offense'])
            ->where('reporter_id', $staffId)
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'reports_page');

        // Get violation records (cases) reported by this staff member
        $cases = ViolationRecord::with(['student', 'offenseRule', 'decisionMaker'])
            ->where('reported_by', $staffId)
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'cases_page');

        return view('staff.my-reports', compact('reports', 'cases'));
    }
}
