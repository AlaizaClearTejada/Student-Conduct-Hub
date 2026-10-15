<?php

namespace App\Jobs;

use App\Models\TribunalCase;
use App\Services\ComplaintLetterOcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TribunalCase $tribunalCase
    ) {}

    public function handle(ComplaintLetterOcrService $ocrService): void
    {
        $disk = $this->tribunalCase->document_disk ?: 'local';
        $absolutePath = Storage::disk($disk)->path($this->tribunalCase->document_path);
        $extractedText = $ocrService->extractText($absolutePath);

        $this->tribunalCase->update([
            'searchable_text' => $extractedText,
        ]);
    }
}
