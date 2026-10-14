<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Models\CaseWorkflowLog;
use App\Models\TribunalCase;
use App\Models\ViolationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TribunalController extends Controller
{
    public function index(): View
    {
        $cases = TribunalCase::query()
            ->forTribunal()
            ->with([
                'incidentReport.student',
                'incidentReport.offense',
                'violationRecord.student',
                'violationRecord.offenseRule',
            ])
            ->latest()
            ->get();

        return view('tribunal.cases.index', compact('cases'));
    }

    public function create(): View
    {
        return view('tribunal.cases.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'case_number' => ['required', 'string', 'max:255', 'unique:tribunal_cases,case_number'],
            'title' => ['required', 'string', 'max:255'],
            'respondent_name' => ['nullable', 'string', 'max:255'],
            'incident_date' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in([CaseStatus::UNDER_INVESTIGATION->value])],
        ]);

        TribunalCase::query()->create([
            ...$validated,
            'is_formal_charge' => true,
        ]);

        return redirect()
            ->route('tribunal.cases.index')
            ->with('success', 'Tribunal case successfully created.');
    }

    public function show(TribunalCase $case): View
    {
        abort_unless($case->isEligibleForTribunal(), 404);

        $case->load([
            'incidentReport.student',
            'incidentReport.offense',
            'violationRecord.student',
            'violationRecord.offenseRule',
            'violationRecord.evidence',
        ]);

        return view('tribunal.cases.show', compact('case'));
    }

    public function document(TribunalCase $case): BinaryFileResponse
    {
        abort_unless($case->isEligibleForTribunal(), 404);

        $disk = $case->document_disk ?: 'local';
        abort_unless($case->document_path && Storage::disk($disk)->exists($case->document_path), 404);

        return response()->file(Storage::disk($disk)->path($case->document_path), [
            'Content-Disposition' => 'inline; filename="'.basename($case->document_path).'"',
        ]);
    }

    public function updateStatus(Request $request, TribunalCase $case): RedirectResponse
    {
        abort_unless($case->isEligibleForTribunal(), 404);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(CaseStatus::class)],
        ]);

        $newStatus = CaseStatus::from($validated['status']);

        if (! in_array($newStatus, $case->status->validTransitions(), true)) {
            return back()->with('error', 'Invalid status transition.');
        }

        DB::transaction(function () use ($case, $newStatus): void {
            $case->transitionTo($newStatus);

            $violationRecord = $case->violationRecord
                ?? ViolationRecord::query()
                    ->where('case_tracking_number', $case->case_number)
                    ->first();

            if ($violationRecord === null) {
                return;
            }

            CaseWorkflowLog::logAction(
                $violationRecord->id,
                auth()->id(),
                'Status Changed',
                'Tribunal updated case status to: '.ucwords(str_replace('_', ' ', $newStatus->value)),
                ['tribunal_status' => $newStatus->value],
            );

            if (
                in_array($newStatus, [CaseStatus::UNDER_INVESTIGATION, CaseStatus::CLOSED], true)
                && in_array($violationRecord->status, ['Pending Review', 'Notice Sent'], true)
            ) {
                $violationRecord->update(['status' => 'Under Investigation']);
            }
        });

        return back()->with('success', 'Case status updated successfully to '.$newStatus->value);
    }
}
