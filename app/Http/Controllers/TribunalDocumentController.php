<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentOcr;
use App\Models\TribunalCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TribunalDocumentController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpeg,png,jpg', 'max:20480'],
            'case_id' => ['required', 'integer', 'exists:tribunal_cases,id'],
        ]);

        $tribunalCase = TribunalCase::query()
            ->forTribunal()
            ->findOrFail($request->integer('case_id'));

        $file = $request->file('document');
        $path = $file->store('tribunal_documents', 'tribunal');

        $tribunalCase->update([
            'document_path' => $path,
            'document_disk' => 'tribunal',
            'searchable_text' => null,
        ]);

        ProcessDocumentOcr::dispatch($tribunalCase->refresh())->afterCommit();

        return response()->json([
            'message' => 'Document uploaded successfully. Processing started in background.',
            'case_id' => $tribunalCase->id,
        ], 202);
    }
}
