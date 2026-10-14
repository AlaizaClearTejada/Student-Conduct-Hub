<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class ComplaintLetterOcrService
{
    public function extractText(string $filePath): string
    {
        $process = new Process([
            config('services.python.binary', 'python'),
            base_path('scripts/ocr_processor.py'),
            $filePath,
        ]);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'The OCR process could not read this image.');
        }

        $text = trim($process->getOutput());

        if ($text === '') {
            throw new RuntimeException('No readable text was found in the uploaded complaint letter.');
        }

        return $text;
    }
}
