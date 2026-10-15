<?php

namespace App\Services;

use App\Models\OffenseRule;
use App\Models\User;
use App\Models\ViolationRecord;
use Illuminate\Support\Collection;

class OffenseProgressionService
{
    public function nextCount(int $studentId, int $offenseId): int
    {
        $progression = ViolationRecord::query()
            ->where('student_id', $studentId)
            ->where('offense_id', $offenseId)
            ->where('status', '!=', 'Dismissed')
            ->selectRaw('COUNT(*) AS record_count, COALESCE(MAX(offense_count), 0) AS max_offense_count, COALESCE(SUM(offense_count_adjustment), 0) AS appeal_adjustment')
            ->firstOrFail();

        return max(
            0,
            (int) $progression->max_offense_count,
            (int) $progression->record_count + (int) $progression->appeal_adjustment
        ) + 1;
    }

    /**
     * @return array{
     *     next_count: int,
     *     next_level: string,
     *     next_sanction: string,
     *     records: list<array{
     *         case_number: string,
     *         incident_date: string,
     *         offense_count: int,
     *         offense_level: string,
     *         status: string,
     *         sanction: ?string
     *     }>
     * }
     */
    public function preview(int $studentId, OffenseRule $offense): array
    {
        $records = ViolationRecord::query()
            ->with('offenseRule')
            ->where('student_id', $studentId)
            ->where('offense_id', $offense->id)
            ->where('status', '!=', 'Dismissed')
            ->latest('date_of_incident')
            ->get();
        $nextCount = $this->nextCount($studentId, $offense->id);

        return [
            'next_count' => $nextCount,
            'next_level' => $this->levelLabel($nextCount),
            'next_sanction' => $offense->getSanctionForOffenseCount($nextCount)
                ?? $offense->standard_sanction,
            'records' => $records
                ->map(fn (ViolationRecord $record): array => [
                    'case_number' => $record->case_tracking_number,
                    'incident_date' => $record->date_of_incident?->format('M d, Y') ?? 'N/A',
                    'offense_count' => (int) ($record->offense_count ?? 1),
                    'offense_level' => $this->levelLabel((int) ($record->offense_count ?? 1)),
                    'status' => $record->status,
                    'sanction' => $record->applied_sanction,
                ])
                ->all(),
        ];
    }

    /**
     * @return Collection<int, array{
     *     offense: OffenseRule,
     *     occurrence_count: int,
     *     active_count: int,
     *     latest_record: ViolationRecord
     * }>
     */
    public function trackerForStudent(User $student): Collection
    {
        return ViolationRecord::query()
            ->with('offenseRule')
            ->where('student_id', $student->id)
            ->where('status', '!=', 'Dismissed')
            ->latest('date_of_incident')
            ->get()
            ->groupBy('offense_id')
            ->map(function (Collection $records): array {
                /** @var ViolationRecord $latestRecord */
                $latestRecord = $records->first();
                $activeCount = $records->whereIn('status', [
                    'Pending Review',
                    'Pending',
                    'Notice Sent',
                    'Under Investigation',
                    'Appealed',
                    'Sanction Active',
                ])->count();

                $occurrenceCount = $this->effectiveCount($records);

                return [
                    'offense' => $latestRecord->offenseRule,
                    'occurrence_count' => $occurrenceCount,
                    'offense_level' => $this->levelLabel($occurrenceCount),
                    'sanction' => $latestRecord->applied_sanction
                        ?: ($latestRecord->offenseRule?->getSanctionForOffenseCount($occurrenceCount)
                            ?? $latestRecord->offenseRule?->standard_sanction),
                    'active_count' => $activeCount,
                    'latest_record' => $latestRecord,
                ];
            })
            ->filter(fn (array $entry): bool => $entry['offense'] !== null)
            ->values();
    }

    /**
     * @param  Collection<int, ViolationRecord>  $records
     */
    private function effectiveCount(Collection $records): int
    {
        $adjustment = $records->sum('offense_count_adjustment');

        return max(
            1,
            (int) $records->max('offense_count'),
            $records->count() + (int) $adjustment
        );
    }

    public function levelLabel(int $count): string
    {
        return match ($count) {
            1 => '1st Offense',
            2 => '2nd Offense',
            default => '3rd Offense / Escalated',
        };
    }
}
