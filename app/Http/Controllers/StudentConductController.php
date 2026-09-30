<?php

namespace App\Http\Controllers;

use App\Models\CaseWorkflowLog;
use App\Models\OffenseRule;
use App\Models\ViolationRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentConductController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the student's conduct dashboard.
     *
     * CRITICAL: Implements strict data isolation - students can ONLY query their own UUID/ID.
     * Utilizes eager loading to prevent N+1 query problems.
     */
    public function index(): View
    {
        $studentId = Auth::id();

        // Eager load related offense details to prevent N+1 queries
        // Uses ->with() to load relationships in a single query
        $violationRecords = ViolationRecord::with(['offenseRule', 'reporter'])
            ->where('student_id', $studentId)
            ->orderBy('date_of_incident', 'desc')
            ->get();

        // Calculate dynamic conduct standing
        $activeSanctionsCount = $violationRecords->where('status', 'Sanction Active')->count();
        $standing = $activeSanctionsCount > 0 ? 'Under Sanction' : 'Good Standing';

        // Additional dashboard metrics
        $pendingReviewCount = $violationRecords->whereIn('status', ['Pending Review', 'Pending', 'Notice Sent'])->count();
        $resolvedCount = $violationRecords->whereIn('status', ['Resolved', 'Dismissed', 'Warning'])->count();
        $appealedCount = $violationRecords->where('status', 'Appealed')->count();

        return view('student.dashboard', compact(
            'violationRecords',
            'standing',
            'activeSanctionsCount',
            'pendingReviewCount',
            'resolvedCount',
            'appealedCount'
        ));
    }

    /**
     * Display a specific violation record.
     *
     * CRITICAL: Authorization gate prevents IDOR vulnerability.
     * URL manipulation (e.g., /student/records/15 → /student/records/16) is blocked by policy.
     */
    public function show(ViolationRecord $record): View
    {
        // Policy check: ensures authenticated user owns this record
        $this->authorize('view', $record);

        // Eager load relationships for detailed view
        $record->load(['offenseRule', 'reporter']);

        return view('student.record-detail', compact('record'));
    }

    /**
     * Display all conduct records for the authenticated student.
     *
     * This is a dedicated records page separate from the dashboard.
     */
    public function records(): View
    {
        $studentId = Auth::id();

        // Eager load related offense details to prevent N+1 queries
        $violationRecords = ViolationRecord::with(['offenseRule', 'reporter'])
            ->where('student_id', $studentId)
            ->orderBy('date_of_incident', 'desc')
            ->get();

        // Calculate metrics for summary cards
        $activeSanctionsCount = $violationRecords->where('status', 'Sanction Active')->count();
        $pendingReviewCount = $violationRecords->where('status', 'Pending Review')->count();
        $resolvedCount = $violationRecords->where('status', 'Resolved')->count();
        $appealedCount = $violationRecords->where('status', 'Appealed')->count();

        return view('student.records', compact(
            'violationRecords',
            'activeSanctionsCount',
            'pendingReviewCount',
            'resolvedCount',
            'appealedCount'
        ));
    }

    /**
     * Display the student's own profile with academic and conduct information.
     */
    public function profile(): View
    {
        $user = Auth::user();
        $studentId = $user->id;

        $violationRecords = ViolationRecord::where('student_id', $studentId)->get();

        $activeSanctionsCount = $violationRecords->where('status', 'Sanction Active')->count();
        $pendingCount = $violationRecords->where('status', 'Pending Review')->count();
        $resolvedCount = $violationRecords->where('status', 'Resolved')->count();
        $totalRecords = $violationRecords->count();
        $conductStanding = $activeSanctionsCount > 0 ? 'Under Sanction' : 'Good Standing';

        return view('student.profile', compact(
            'user',
            'activeSanctionsCount',
            'pendingCount',
            'resolvedCount',
            'totalRecords',
            'conductStanding'
        ));
    }

    /**
     * Display all offense rules from the CSU Student Manual.
     *
     * Students can browse all university policies and offense codes.
     */
    public function offenseRules(): View
    {
        // Get all active offense rules ordered by code
        $offenseRules = OffenseRule::where('is_active', true)
            ->orderBy('code')
            ->get();

        // Get unique categories and severity levels for filters
        $categories = OffenseRule::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        $severityLevels = OffenseRule::where('is_active', true)
            ->distinct()
            ->pluck('severity_level')
            ->sort()
            ->values();

        $offenseJson = json_encode(
            $offenseRules->map(fn ($o) => [
                'code' => $o->code,
                'title' => $o->title,
                'description' => $o->description,
                'category' => $o->category,
                'severity_level' => $o->severity_level,
                'gravity' => $o->gravity,
                'standard_sanction' => $o->standard_sanction,
                'first_offense_sanction' => $o->first_offense_sanction,
                'second_offense_sanction' => $o->second_offense_sanction,
                'third_offense_sanction' => $o->third_offense_sanction,
                'legal_reference' => $o->legal_reference,
                'requires_tribunal' => $o->requires_tribunal,
            ])->values(),
            JSON_HEX_TAG | JSON_HEX_AMP
        );

        return view('student.offense-rules', compact(
            'offenseRules',
            'categories',
            'severityLevels',
            'offenseJson'
        ));
    }

    /**
     * Submit the student's written answer/defense for a case.
     */
    public function submitAnswer(Request $request, ViolationRecord $record): RedirectResponse
    {
        $this->authorize('submitAnswer', $record);

        $request->validate([
            'student_answer' => 'required|string|min:20|max:5000',
        ], [
            'student_answer.required' => 'Please write your response before submitting.',
            'student_answer.min' => 'Your response must be at least 20 characters.',
            'student_answer.max' => 'Your response cannot exceed 5,000 characters.',
        ]);

        $record->update([
            'student_answer' => $request->student_answer,
            'student_answer_submitted_date' => now()->toDateString(),
            'status' => 'Under Investigation',
        ]);

        CaseWorkflowLog::logAction(
            $record->id,
            Auth::id(),
            'Student Answer Submitted',
            'Student submitted written answer/defense',
            ['answer_length' => strlen($request->student_answer)]
        );

        return redirect()->route('student.records.show', $record)
            ->with('success', 'Your written response has been submitted successfully.');
    }
}
