<?php

namespace Tests\Feature;

use App\Services\ComplaintLetterOcrService;
use RuntimeException;
use Tests\TestCase;

class ComplaintLetterOcrServiceTest extends TestCase
{
    public function test_ocr_failure_explains_manual_entry_and_administrator_configuration(): void
    {
        config([
            'services.python.path' => 'C:\\missing-python\\python.exe',
            'services.python.binary' => null,
        ]);
        $originalPath = getenv('PATH');
        putenv('PATH=');

        try {
            app(ComplaintLetterOcrService::class)->extractText(
                storage_path('app/testing/missing-complaint-letter.png')
            );
            $this->fail('OCR should fail when the input document is unavailable.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('no Python executable could be started', $exception->getMessage());
            $this->assertStringContainsString('manually without OCR', $exception->getMessage());
            $this->assertStringContainsString('Ask an administrator', $exception->getMessage());
            $this->assertStringContainsString('PYTHON_PATH', $exception->getMessage());
        } finally {
            putenv($originalPath === false ? 'PATH' : 'PATH='.$originalPath);
        }
    }
}
