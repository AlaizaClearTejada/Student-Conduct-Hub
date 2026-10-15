<?php

namespace App\Services;

use App\Enums\CaseStatus;
use App\Models\IncidentReport;
use App\Models\TribunalCase;

class TribunalCaseRegistrar
{
    public function registerEligibleReport(IncidentReport $report): ?TribunalCase
    {
        $report->loadMissing(['student', 'offense']);

        $isMajorOffense = strcasecmp((string) $report->offense?->severity_level, 'Major') === 0;
        $isFormalCharge = $report->report_type === 'Formal Charge';

        if (! $isFormalCharge && ! $isMajorOffense) {
            return null;
        }

        return TribunalCase::query()->firstOrCreate(
            ['case_number' => $report->tracking_number],
            [
                'incident_report_id' => $report->id,
                'title' => 'Disciplinary Case: '.($report->offense?->title ?? 'Formal Charge'),
                'respondent_name' => $report->student?->name ?? 'Unknown Student',
                'category' => $report->offense?->category ?? 'General',
                'priority' => 'high',
                'description' => $report->description,
                'status' => CaseStatus::UNDER_INVESTIGATION,
                'is_formal_charge' => true,
            ]
        );
    }
}
