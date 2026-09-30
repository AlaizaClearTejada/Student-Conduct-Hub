<?php

namespace App\Services\Notifications;

use App\Models\TribunalCase;
use App\Models\User;
use App\Models\ViolationRecord;
use Illuminate\Support\Collection;

class RecipientMapper
{
    /**
     * Maps an array of role strings to a collection of actual User objects.
     */
    public function map(array $roles, array $context): Collection
    {
        $recipients = collect();
        $caseId = $context['case_id'] ?? null;

        foreach ($roles as $role) {
            switch ($role) {
                case 'accused':
                    if ($caseId) {
                        // Find the student linked to this case
                        // Assuming TribunalCase -> ViolationRecord -> student_id
                        $case = TribunalCase::find($caseId);
                        // In reality, this depends on exact relations, we mock it via ViolationRecord or directly
                        $violation = ViolationRecord::where('tribunal_case_id', $caseId)->first();
                        if ($violation && $violation->student) {
                            $recipients->push($violation->student);
                        }
                    }
                    break;

                case 'complainant':
                    if ($caseId) {
                        $violation = ViolationRecord::where('tribunal_case_id', $caseId)->first();
                        if ($violation && $violation->reporter) {
                            $recipients->push($violation->reporter);
                        }
                    }
                    break;

                case 'tribunal_chair':
                case 'tribunal':
                case 'osdw_staff':
                    // Fetch users with specific system roles using Spatie Permission
                    $users = User::role(str_replace('_', ' ', $role))->get();
                    $recipients = $recipients->concat($users);
                    break;
            }
        }

        return $recipients->unique('id')->values();
    }

    /**
     * Determines the conceptual role of a user in the context of an event.
     */
    public function determineRole(User $user, array $context): string
    {
        // simplistic role determination
        if ($user->hasRole('student')) {
            return 'student';
        }
        if ($user->hasRole('tribunal panel') || $user->hasRole('tribunal chair')) {
            return 'tribunal';
        }

        return 'staff';
    }
}
