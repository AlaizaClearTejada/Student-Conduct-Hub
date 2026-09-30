<?php

namespace App\Http\Controllers;

use App\Enums\CaseStatus;
use App\Jobs\ProcessDocumentOcr;
use App\Models\TribunalCase;
use App\Models\ViolationRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TribunalDocumentController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:20480'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $file = $request->file('document');
        $path = $file->store('tribunal_documents', 'tribunal');

        $tribunalCase = TribunalCase::create([
            'case_number' => ViolationRecord::generateCaseTrackingNumber(),
            'title' => $request->input('title') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'description' => $request->input('description'),
            'status' => CaseStatus::UNDER_INFORMAL_DISCUSSION,
            'document_path' => $path,
            'document_disk' => 'tribunal',
            'searchable_text' => null,
        ]);

        ProcessDocumentOcr::dispatch($tribunalCase)->afterCommit();

        return response()->json([
            'message' => 'Document uploaded successfully. Processing started in background.',
            'case_id' => $tribunalCase->id,
        ], 202);
    }
}
