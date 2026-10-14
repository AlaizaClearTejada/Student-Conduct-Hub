<?php

namespace App\Livewire\Staff;

use App\Models\OffenseRule;
use App\Services\ComplaintLetterOcrService;
use App\Services\OffenseRuleSuggestionService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class SmartIncidentAssistance extends Component
{
    use WithFileUploads;

    public string $search = '';

    public $complaintLetter = null;

    public string $extractedText = '';

    public ?string $extractedStudentId = null;

    public ?string $extractedComplainant = null;

    public ?int $suggestedOffenseId = null;

    public ?string $ocrNotice = null;

    public function updatedComplaintLetter(): void
    {
        $this->validate([
            'complaintLetter' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        try {
            $path = $this->complaintLetter->getRealPath() ?: $this->complaintLetter->getPathname();
            $this->extractedText = app(ComplaintLetterOcrService::class)->extractText($path);
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'complaintLetter' => 'Complaint letter OCR failed: '.$exception->getMessage(),
            ]);
        }

        $ocrService = app(ComplaintLetterOcrService::class);
        $this->extractedStudentId = $ocrService->extractRespondentStudentId($this->extractedText);
        $this->extractedComplainant = $ocrService->extractComplainantName($this->extractedText);
        $this->search = $this->extractedText;
        $this->suggestedOffenseId = $this->getOffenseSuggestionsProperty()->first()?->id;
        $this->ocrNotice = $this->suggestedOffenseId === null
            ? 'Text was extracted. Search and select the closest handbook offense before continuing.'
            : 'Text was extracted. Review the suggested offense and student details before continuing.';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, OffenseRule>
     */
    public function getOffenseSuggestionsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return app(OffenseRuleSuggestionService::class)->suggest($this->search);
    }

    public function selectOffense(int $offenseId): void
    {
        $offense = OffenseRule::query()->active()->findOrFail($offenseId);
        $this->continueWithOffense($offense);
    }

    public function continueWithSuggestion(): void
    {
        if ($this->suggestedOffenseId === null) {
            throw ValidationException::withMessages([
                'search' => 'Search for and select a matching handbook offense before continuing.',
            ]);
        }

        $offense = OffenseRule::query()->active()->findOrFail($this->suggestedOffenseId);
        $this->continueWithOffense($offense);
    }

    private function continueWithOffense(OffenseRule $offense): void
    {
        $studentId = $this->extractedStudentId;
        $description = $this->extractedText;

        if ($offense->requiresTribunalReview() || $offense->gravity !== 'minor') {
            session()->flash('smart_incident_intake', [
                'student_id' => $studentId,
                'complainant' => $this->extractedComplainant,
                'description' => $description !== '' ? mb_substr($description, 0, 5000) : null,
            ]);

            $this->redirect(route('staff.formal-charge', ['offense' => $offense->id]), navigate: true);

            return;
        }

        $this->dispatch(
            'open-quick-log-modal',
            offenseId: $offense->id,
            studentId: $studentId,
            complainant: $this->extractedComplainant,
            description: $description !== '' ? mb_substr($description, 0, 5000) : null,
        );
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.staff.smart-incident-assistance');
    }
}
