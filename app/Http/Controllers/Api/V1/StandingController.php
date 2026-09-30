<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StandingController extends Controller
{
    /**
     * Display the specified student's standing.
     */
    public function show(User $student): JsonResponse
    {
        if (! $student->hasRole('student')) {
            return response()->json(['message' => 'User is not a student'], 404);
        }

        $standing = $student->standing()->firstOrCreate([
            'student_id' => $student->id,
        ], [
            'standing_status' => 'GOOD',
            'active_cases_count' => 0,
            'major_offenses_count' => 0,
            'standing_computed_at' => now(),
        ]);

        return response()->json([
            'standing' => $standing,
        ]);
    }

    /**
     * Display the history of standing changes (audit logs).
     */
    public function history(User $student): JsonResponse
    {
        if (! $student->hasRole('student')) {
            return response()->json(['message' => 'User is not a student'], 404);
        }

        $standing = $student->standing;

        if (! $standing) {
            return response()->json(['history' => []]);
        }

        $logs = \App\Models\AuditLog::where('entity_type', 'students_standing')
            ->where('entity_id', $standing->id)
            ->latest('created_at')
            ->get();

        return response()->json([
            'history' => $logs,
        ]);
    }

    /**
     * Check if the student has a clearance hold.
     */
    public function clearanceHold(User $student): JsonResponse
    {
        if (! $student->hasRole('student')) {
            return response()->json(['message' => 'User is not a student'], 404);
        }

        $standing = $student->standing;

        $hasHold = false;
        $holdReason = null;

        if ($standing) {
            if (in_array($standing->standing_status, ['PROBATION', 'SUSPENDED', 'EXPELLED'])) {
                $hasHold = true;
                $holdReason = 'Student is currently in '.$standing->standing_status.' standing.';
            }
        }

        return response()->json([
            'has_hold' => $hasHold,
            'reason' => $holdReason,
        ]);
    }

    /**
     * Override clearance hold (Admin only).
     */
    public function overrideClearanceHold(Request $request, User $student): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        // Log audit for override
        \App\Models\AuditLog::create([
            'entity_type' => 'users',
            'entity_id' => (string) $student->id,
            'action' => 'CLEARANCE_OVERRIDE',
            'actor_id' => $request->user()->id,
            'changes' => [
                'reason' => $validated['reason'],
            ],
        ]);

        return response()->json([
            'message' => 'Clearance hold overridden successfully.',
        ]);
    }
}
