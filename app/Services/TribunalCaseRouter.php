<?php

namespace App\Services;

use App\Enums\CaseStatus;
use App\Jobs\ProcessDocumentOcr;
use App\Models\IncidentReport;
use App\Models\TribunalCase;
use App\Models\ViolationRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;

class TribunalCaseRouter
{
    public function routeUnlinkedMajorReports(): void
    {
        IncidentReport::query()
            ->whereNotIn('status', ['Resolved', 'Dismissed'])
            ->whereDoesntHave('tribunalCase')
            ->whereHas('offense', fn (Builder $query) => $query->requiringTribunalReview())
            ->with('offense')
            ->get()
            ->each(fn (IncidentReport $report) => $this->routeIncidentReport($report, dispatchOcr: false));
    }

    public function routeUnlinkedMajorViolationRecords(): void
    {
        ViolationRecord::query()
            ->where('status', '!=', 'Resolved')
            ->whereDoesntHave('tribunalCase')
            ->whereHas('offenseRule', fn (Builder $query) => $query->requiringTribunalReview())
            ->with(['offenseRule', 'evidence'])
            ->get()
            ->each(fn (ViolationRecord $record) => $this->routeViolationRecord($record, dispatchOcr: false));
    }

    public function routeIncidentReport(IncidentReport $report, bool $dispatchOcr = true): TribunalCase
    {
        $report->loadMissing('offense');

        $case = TribunalCase::query()->where('incident_report_id', $report->id)->first()
            ?? $this->firstOrCreateWithNumberRetry(
                ['incident_report_id' => $report->id],
                [
                    'case_number' => ViolationRecord::generateCaseTrackingNumber(),
                    'title' => $report->offense->title,
                    'description' => $report->description,
                    'status' => CaseStatus::UNDER_FORMAL_INVESTIGATION,
                    'document_path' => $report->evidence_path,
                    'document_disk' => 'local',
                ]
            );

        if ($case->wasRecentlyCreated && $dispatchOcr) {
            $this->queueOcr($case);
        }

        return $case;
    }

    public function routeViolationRecord(
        ViolationRecord $record,
        ?IncidentReport $report = null,
        ?string $documentPath = null,
        bool $dispatchOcr = true
    ): ?TribunalCase {
        $record->loadMissing(['offenseRule', 'evidence']);

        if ($record->offenseRule?->requiresTribunalReview() !== true) {
            return null;
        }

        $case = $this->findLinkedCase($record, $report);
        $wasRecentlyCreated = ! $case->exists;

        if (! $case->exists) {
            $caseNumber = $record->case_tracking_number;

            if (TribunalCase::query()->where('case_number', $caseNumber)->exists()) {
                $caseNumber = ViolationRecord::generateCaseTrackingNumber();
            }

            try {
                $case = $this->firstOrCreateWithNumberRetry(
                    ['violation_record_id' => $record->id],
                    [
                        'incident_report_id' => $report?->id,
                        'case_number' => $caseNumber,
                        'title' => $record->offenseRule?->title ?? 'Disciplinary Violation',
                        'description' => $record->incident_description,
                        'status' => CaseStatus::UNDER_FORMAL_INVESTIGATION,
                        'document_path' => $documentPath ?? $report?->evidence_path ?? $record->evidence->first()?->file_path,
                        'document_disk' => 'local',
                    ]
                );
            } catch (UniqueConstraintViolationException $exception) {
                $case = $this->findLinkedCase($record, $report);

                if (! $case->exists) {
                    throw $exception;
                }
            }

            $wasRecentlyCreated = $case->wasRecentlyCreated;
        }

        $case->fill([
            'incident_report_id' => $report?->id ?? $case->incident_report_id,
            'violation_record_id' => $record->id,
            'case_number' => $case->case_number ?: $record->case_tracking_number,
            'title' => $record->offenseRule?->title ?? 'Disciplinary Violation',
            'description' => $record->incident_description,
            'status' => $case->status ?? CaseStatus::UNDER_FORMAL_INVESTIGATION,
            'document_path' => $documentPath
                ?? $report?->evidence_path
                ?? $case->document_path
                ?? $record->evidence->first()?->file_path,
            'document_disk' => $case->document_disk ?: 'local',
        ])->save();

        if ($wasRecentlyCreated && $dispatchOcr) {
            $this->queueOcr($case);
        }

        return $case;
    }

    private function findLinkedCase(ViolationRecord $record, ?IncidentReport $report): TribunalCase
    {
        return TribunalCase::query()
            ->where(function (Builder $query) use ($record, $report): void {
                $query->where('violation_record_id', $record->id);

                if ($report !== null) {
                    $query->orWhere('incident_report_id', $report->id);
                }
            })
            ->first() ?? new TribunalCase;
    }

    /**
     * @param  array<string, mixed>  $source
     * @param  array<string, mixed>  $values
     */
    private function firstOrCreateWithNumberRetry(array $source, array $values): TribunalCase
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return TribunalCase::firstOrCreate($source, $values);
            } catch (UniqueConstraintViolationException $exception) {
                $existing = TribunalCase::query()->where($source)->first();

                if ($existing !== null) {
                    return $existing;
                }

                if (! TribunalCase::query()->where('case_number', $values['case_number'])->exists()) {
                    throw $exception;
                }

                $values['case_number'] = ViolationRecord::generateCaseTrackingNumber();
            }
        }

        throw new \RuntimeException('Could not generate a unique Tribunal case number after five attempts.');
    }

    private function queueOcr(TribunalCase $case): void
    {
        $extension = strtolower(pathinfo($case->document_path ?? '', PATHINFO_EXTENSION));

        if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            ProcessDocumentOcr::dispatch($case)->afterCommit();
        }
    }
}
