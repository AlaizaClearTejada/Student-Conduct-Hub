<?php

namespace App\Jobs;

use App\Models\TribunalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProcessDocumentOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public TribunalCase $tribunalCase
    ) {}

    public function handle(): void
    {
        $disk = $this->tribunalCase->document_disk ?: 'local';
        $absolutePath = Storage::disk($disk)->path($this->tribunalCase->document_path);

        $scriptPath = base_path('scripts/ocr_processor.py');

        $pythonBinary = config('services.python.binary', 'python');
        $process = new Process([$pythonBinary, $scriptPath, $absolutePath]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $extractedText = trim($process->getOutput());

        $this->tribunalCase->update([
            'searchable_text' => $extractedText,
        ]);
    }
}
