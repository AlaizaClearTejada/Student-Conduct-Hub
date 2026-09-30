<?php

namespace App\Listeners;

use App\Events\OffenseStatusChanged;
use App\Models\Offense;
use App\Models\StudentStanding;

class EvaluateStudentStanding
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OffenseStatusChanged $event): void
    {
        $offense = $event->offense;
        $studentId = $offense->student_id;

        $studentOffenses = Offense::where('student_id', $studentId)->get();

        $standingStatus = 'GOOD';
        $activeCasesCount = 0;
        $majorOffensesCount = 0;
        $criticalOffensesCount = 0;

        $pendingSanctions = [];

        foreach ($studentOffenses as $off) {
            if (in_array($off->status, ['SUBMITTED', 'UNDER_INVESTIGATION', 'APPEALED'])) {
                $activeCasesCount++;
            }

            if ($off->status === 'RESOLVED') {
                if ($off->offense_type === 'MAJOR') {
                    $majorOffensesCount++;
                } elseif ($off->offense_type === 'CRITICAL') {
                    $criticalOffensesCount++;
                }

                if ($off->sanction_details && isset($off->sanction_details['active']) && $off->sanction_details['active'] == true) {
                    $pendingSanctions[] = $off->sanction_details;
                }
            }
        }

        // Logic for calculating standing
        if ($criticalOffensesCount > 0) {
            // Check if there is an expelled sanction
            $isExpelled = collect($studentOffenses)->contains(function ($o) {
                return $o->offense_type === 'CRITICAL'
                    && $o->status === 'RESOLVED'
                    && isset($o->sanction_details['type'])
                    && $o->sanction_details['type'] === 'EXPULSION';
            });

            if ($isExpelled) {
                $standingStatus = 'EXPELLED';
            } else {
                $standingStatus = 'SUSPENDED';
            }
        } elseif ($majorOffensesCount >= 2) {
            $standingStatus = 'SUSPENDED';
        } elseif ($majorOffensesCount >= 1 || count($pendingSanctions) > 0) {
            $standingStatus = 'PROBATION';
        } elseif ($activeCasesCount > 0) {
            $standingStatus = 'REVIEW';
        }

        // Update the standing
        StudentStanding::updateOrCreate(
            ['student_id' => $studentId],
            [
                'standing_status' => $standingStatus,
                'active_cases_count' => $activeCasesCount,
                'major_offenses_count' => $majorOffensesCount,
                'pending_sanctions' => $pendingSanctions,
                'standing_computed_at' => now(),
            ]
        );
    }
}
