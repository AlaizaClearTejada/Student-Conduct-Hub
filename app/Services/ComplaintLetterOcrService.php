<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ComplaintLetterOcrService
{
    public function extractRespondentStudentId(string $text): ?string
    {
        $respondentPatterns = [
            '/\b(?:witnessed|reported|saw)\s+(?:the\s+)?student\b.{0,180}?\b(\d{2}-\d{5})\b/is',
            '/\bagainst\b.{0,160}?\b(\d{2}-\d{5})\b/is',
            '/\boffender\b.{0,160}?\b(\d{2}-\d{5})\b/is',
            '/\bcaught\b.{0,160}?\b(\d{2}-\d{5})\b/is',
        ];

        foreach ($respondentPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                return $matches[1];
            }
        }

        $studentIds = $this->extractStudentIds($text);
        $complainantId = $this->extractComplainantStudentId($text);
        $respondentIds = array_values(array_filter(
            $studentIds,
            fn (string $studentId): bool => $studentId !== $complainantId
        ));

        if ($respondentIds !== []) {
            return $respondentIds[0];
        }

        return $complainantId === null ? ($studentIds[0] ?? null) : null;
    }

    public function extractComplainantName(string $text): ?string
    {
        $patterns = [
            '/\bI\s*,\s*([^\r\n(]{2,100}?)\s*\(\s*student\s*id\s*[:#]?\s*\d{2}-\d{5}\s*\)/i',
            '/^(?:complainant|reported\s+by|from)\s*[:\-]\s*(.+)$/im',
            '/\bSincerely\s*[,;:]?\s*(?:\r?\n\s*)?([A-Z][\p{L} .\'-]{1,100})/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches) === 1) {
                $name = trim($matches[1], " \t\n\r\0\x0B,;:-");

                if ($name !== '') {
                    return mb_substr($name, 0, 255);
                }
            }
        }

        return null;
    }

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

    private function extractComplainantStudentId(string $text): ?string
    {
        if (preg_match('/\bI\s*,\s*[^\r\n(]{2,100}?\s*\(\s*student\s*id\s*[:#]?\s*(\d{2}-\d{5})\s*\)/i', $text, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/\bSincerely\b/i', $text, $signature, PREG_OFFSET_CAPTURE) !== 1) {
            return null;
        }

        $signatureText = substr($text, $signature[0][1]);

        return preg_match('/\b\d{2}-\d{5}\b/', $signatureText, $studentId) === 1
            ? $studentId[0]
            : null;
    }

    /**
     * @return list<string>
     */
    private function extractStudentIds(string $text): array
    {
        preg_match_all('/\b\d{2}-\d{5}\b/', $text, $matches);

        return array_values(array_unique($matches[0]));
    }
}
