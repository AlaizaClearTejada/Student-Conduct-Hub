<?php

namespace App\Livewire\Tribunal;

use App\Models\TribunalCase;
use App\Services\ComplaintLetterOcrService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RuntimeException;

class CaseOcrText extends Component
{
    #[Locked]
    public int $caseId;

    public string $ocrText = '';

    public ?string $ocrError = null;

    public bool $hasDocument = false;

    public function mount(TribunalCase $case, ComplaintLetterOcrService $ocrService): void
    {
        $this->authorizeAccess();
        $case = TribunalCase::query()
            ->forTribunal()
            ->findOrFail($case->getKey());
        $this->caseId = $case->id;
        $this->ocrText = (string) ($case->searchable_text ?? '');
        $this->hasDocument = filled($case->document_path);

        if ($this->ocrText === '' && $this->hasDocument) {
            $this->runOcr($case, $ocrService);
        }
    }

    public function retryOcr(ComplaintLetterOcrService $ocrService): void
    {
        $this->authorizeAccess();

        $case = TribunalCase::query()
            ->forTribunal()
            ->findOrFail($this->caseId);

        $this->hasDocument = filled($case->document_path);

        if (! $this->hasDocument) {
            $this->ocrError = 'This case does not have an attachment available for OCR.';

            return;
        }

        $this->runOcr($case, $ocrService);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.tribunal.case-ocr-text');
    }

    private function runOcr(TribunalCase $case, ComplaintLetterOcrService $ocrService): void
    {
        $this->ocrText = '';
        $this->ocrError = null;

        try {
            $disk = $case->document_disk ?: 'local';
            $filePath = Storage::disk($disk)->path($case->document_path);
            $text = trim($ocrService->extractText($filePath));

            if ($text === '') {
                throw new RuntimeException('No readable text was found in the case attachment.');
            }

            $case->update(['searchable_text' => $text]);
            $this->ocrText = $text;
        } catch (RuntimeException $exception) {
            $this->ocrError = $exception->getMessage();
        }
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user !== null
                && ($user->can('access tribunal module') || $user->hasAnyRole(['administrator', 'osdw_admin'])),
            403
        );
    }
}
