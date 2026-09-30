<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\OffenseStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\DigitalEvidence;
use App\Models\Offense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OffenseController extends Controller
{
    /**
     * Display a listing of the offenses.
     */
    public function index(Request $request): JsonResponse
    {
        $offenses = Offense::with(['student', 'reporter', 'tribunalMember'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->student_id, fn ($q, $id) => $q->where('student_id', $id))
            ->latest()
            ->paginate($request->per_page ?? 50);

        return response()->json($offenses);
    }

    /**
     * Store a newly created offense in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'offense_type' => ['required', 'string', 'in:MINOR,MAJOR,CRITICAL'],
            'offense_category' => ['required', 'string'],
            'incident_date' => ['required', 'date', 'before_or_equal:today'],
            'incident_time' => ['required', 'date_format:H:i'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50', 'max:5000'],
            'witnesses' => ['nullable', 'array'],
        ]);

        $offense = Offense::create(array_merge($validated, [
            'filed_by' => $request->user()->id,
            'case_number' => 'CASE-'.strtoupper(Str::random(10)),
            'status' => 'SUBMITTED',
        ]));

        // Log audit
        \App\Models\AuditLog::create([
            'entity_type' => 'offenses',
            'entity_id' => $offense->id,
            'action' => 'CREATE',
            'actor_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Offense report filed successfully.',
            'offense' => $offense,
        ], 201);
    }

    /**
     * Display the specified offense.
     */
    public function show(Offense $offense): JsonResponse
    {
        return response()->json([
            'offense' => $offense->load(['student', 'reporter', 'tribunalMember', 'digitalEvidence']),
        ]);
    }

    /**
     * Update the specified offense in storage.
     */
    public function update(Request $request, Offense $offense): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:DRAFT,SUBMITTED,UNDER_INVESTIGATION,RESOLVED,APPEALED'],
            'tribunal_assigned_to' => ['sometimes', 'exists:users,id'],
            'tribunal_decision' => ['sometimes', 'string'],
            'sanction_details' => ['sometimes', 'array'],
        ]);

        $oldStatus = $offense->status;
        $offense->update($validated);

        if (isset($validated['status']) && $oldStatus !== $validated['status']) {
            // Dispatch event to recalculate student standing
            event(new OffenseStatusChanged($offense));
        }

        // Log audit
        \App\Models\AuditLog::create([
            'entity_type' => 'offenses',
            'entity_id' => $offense->id,
            'action' => 'UPDATE',
            'actor_id' => $request->user()->id,
            'changes' => $validated,
        ]);

        return response()->json([
            'message' => 'Offense updated successfully',
            'offense' => $offense->fresh()->load(['student', 'reporter', 'tribunalMember']),
        ]);
    }

    /**
     * Upload evidence for an offense.
     */
    public function uploadEvidence(Request $request, Offense $offense): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,docx,xlsx'],
        ]);

        $file = $request->file('file');
        $path = $file->store('evidence', 'local');

        $evidence = $offense->digitalEvidence()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            's3_key' => $path, // using local path for now as per plan
            'file_size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
            'virus_scan_status' => 'PENDING',
        ]);

        // Log audit
        \App\Models\AuditLog::create([
            'entity_type' => 'digital_evidence',
            'entity_id' => $evidence->id,
            'action' => 'CREATE',
            'actor_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Evidence uploaded successfully',
            'evidence' => $evidence,
        ], 201);
    }

    /**
     * Delete evidence.
     */
    public function deleteEvidence(Request $request, Offense $offense, DigitalEvidence $evidence): JsonResponse
    {
        if ($evidence->offense_id !== $offense->id) {
            return response()->json(['message' => 'Evidence not found for this offense'], 404);
        }

        $evidence->delete();

        // Log audit
        \App\Models\AuditLog::create([
            'entity_type' => 'digital_evidence',
            'entity_id' => $evidence->id,
            'action' => 'DELETE',
            'actor_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Evidence deleted successfully']);
    }
}
