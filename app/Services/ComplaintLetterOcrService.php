<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ComplaintLetterOcrService
{
    public function extractText(string $filePath): string
    {
        $scriptPath = base_path('scripts/ocr_processor.py');
        $startErrors = [];
        $executableFinder = new ExecutableFinder;

        foreach ($this->pythonBinaries() as $pythonBinary) {
            $executable = is_file($pythonBinary) ? $pythonBinary : $executableFinder->find($pythonBinary);

            if ($executable === null) {
                $startErrors[] = "Python executable [{$pythonBinary}] was not found.";

                continue;
            }

            $environment = array_filter([
                'TESSERACT_CMD' => config('services.python.tesseract_cmd'),
            ], fn ($value): bool => is_string($value) && trim($value) !== '');
            $process = new Process([$executable, $scriptPath, $filePath], env: $environment);
            $process->setTimeout(300);

            try {
                $process->run();
            } catch (ProcessStartFailedException $exception) {
                $startErrors[] = $exception->getMessage();

                continue;
            }

            if (! $process->isSuccessful()) {
                $error = trim($process->getErrorOutput());
                $diagnostic = trim($error."\n".$process->getOutput());

                if ($this->isPythonUnavailable($diagnostic)) {
                    $startErrors[] = $diagnostic;

                    continue;
                }

                throw new RuntimeException($this->manualProcessingGuidance(
                    $error !== '' ? 'OCR processing failed: '.$error : 'The OCR process could not read this image.'
                ));
            }

            $text = trim($process->getOutput());

            if ($text === '') {
                throw new RuntimeException('No readable text was found in the uploaded complaint letter.');
            }

            return $text;
        }

        $detail = $startErrors === [] ? '' : ' '.$startErrors[array_key_last($startErrors)];

        throw new RuntimeException($this->manualProcessingGuidance(
            'OCR is unavailable because no Python executable could be started.'.$detail
        ));
    }

    /**
     * @return list<string>
     */
    private function pythonBinaries(): array
    {
        $configuredBinaries = [
            config('services.python.path'),
            config('services.python.binary'),
        ];
        $binaries = array_merge($configuredBinaries, ['python', 'python3']);
        $binaries = array_filter($binaries, fn ($binary): bool => is_string($binary) && trim($binary) !== '');

        return array_values(array_unique(array_map('trim', $binaries)));
    }

    private function manualProcessingGuidance(string $message): string
    {
        return $message.' You can continue by entering the complaint details manually without OCR. '
            .'Ask an administrator to configure PYTHON_PATH (or PYTHON_BINARY), install the OCR dependencies, '
            .'and set TESSERACT_CMD if Tesseract is not available on the system PATH.';
    }

    private function isPythonUnavailable(string $error): bool
    {
        return preg_match(
            '/python was not found|python is not recognized as an internal or external command/i',
            $error
        ) === 1;
    }
}
