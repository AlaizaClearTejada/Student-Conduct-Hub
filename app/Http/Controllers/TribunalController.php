<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Models\TribunalCase;
use App\Services\TribunalCaseRouter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TribunalController extends Controller
{
    public function index(): View
    {
        $router = app(TribunalCaseRouter::class);
        $router->routeUnlinkedMajorReports();
        $router->routeUnlinkedMajorViolationRecords();

        $cases = TribunalCase::query()
            ->where('status', '!=', CaseStatus::CASE_RESOLVED->value)
            ->where(function (Builder $query): void {
                $query->whereHas('incidentReport', fn (Builder $reportQuery) => $reportQuery
                    ->whereNotIn('status', ['Resolved', 'Dismissed'])
                    ->whereHas('offense', fn (Builder $offenseQuery) => $offenseQuery->requiringTribunalReview()))
                    ->orWhereHas('violationRecord', fn (Builder $violationQuery) => $violationQuery
                        ->whereNot('status', 'Resolved')
                        ->whereHas('offenseRule', fn (Builder $offenseQuery) => $offenseQuery->requiringTribunalReview()));
            })
            ->with(['incidentReport.offense', 'violationRecord.offenseRule'])
            ->latest()
            ->get();

        return view('tribunal.cases.index', compact('cases'));
    }

    public function show(TribunalCase $case): View
    {
        abort_unless($case->exists, 404);

        $case->load(['incidentReport.offense', 'violationRecord.offenseRule', 'violationRecord.evidence']);

        return view('tribunal.cases.show', compact('case'));
    }

    public function document(TribunalCase $case): BinaryFileResponse
    {
        $disk = $case->document_disk ?: 'local';
        abort_unless($case->document_path && Storage::disk($disk)->exists($case->document_path), 404);

        return response()->file(Storage::disk($disk)->path($case->document_path), [
            'Content-Disposition' => 'inline; filename="'.basename($case->document_path).'"',
        ]);
    }

    public function updateStatus(Request $request, TribunalCase $case)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $newStatus = \App\Enums\CaseStatus::tryFrom($request->status);

        if (! $newStatus) {
            return back()->with('error', 'Invalid status provided.');
        }

        try {
            $case->transitionTo($newStatus);

            return back()->with('success', 'Case status updated successfully to '.$newStatus->value);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
