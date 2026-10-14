<?php

namespace Tests\Feature;

use App\Livewire\Tribunal\CaseOcrText;
use App\Models\TribunalCase;
use App\Models\User;
use App\Services\ComplaintLetterOcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TribunalCaseOcrTextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web']);
    }

    public function test_case_review_runs_ocr_on_mount_and_saves_searchable_text(): void
    {
        Storage::fake('tribunal')->put('tribunal_documents/complaint.pdf', 'document');
        $admin = User::factory()->administrator()->create();
        $admin->assignRole('administrator');
        $case = TribunalCase::factory()->create([
            'is_formal_charge' => true,
            'document_path' => 'tribunal_documents/complaint.pdf',
            'document_disk' => 'tribunal',
            'searchable_text' => null,
        ]);

        $ocrService = Mockery::mock(ComplaintLetterOcrService::class);
        $ocrService->shouldReceive('extractText')
            ->once()
            ->with(Storage::disk('tribunal')->path($case->document_path))
            ->andReturn('Respondent statement extracted from attachment.');
        $this->app->instance(ComplaintLetterOcrService::class, $ocrService);

        Livewire::actingAs($admin)
            ->test(CaseOcrText::class, ['case' => $case])
            ->assertSee('Respondent statement extracted from attachment.');

        $this->assertDatabaseHas('tribunal_cases', [
            'id' => $case->id,
            'searchable_text' => 'Respondent statement extracted from attachment.',
        ]);
    }

    public function test_case_review_skips_ocr_when_searchable_text_already_exists(): void
    {
        Storage::fake('tribunal')->put('tribunal_documents/complaint.pdf', 'document');
        $admin = User::factory()->administrator()->create();
        $admin->assignRole('administrator');
        $case = TribunalCase::factory()->create([
            'is_formal_charge' => true,
            'document_path' => 'tribunal_documents/complaint.pdf',
            'document_disk' => 'tribunal',
            'searchable_text' => 'Previously extracted text.',
        ]);

        $ocrService = Mockery::mock(ComplaintLetterOcrService::class);
        $ocrService->shouldNotReceive('extractText');
        $this->app->instance(ComplaintLetterOcrService::class, $ocrService);

        Livewire::actingAs($admin)
            ->test(CaseOcrText::class, ['case' => $case])
            ->assertSee('Previously extracted text.');
    }

    public function test_failed_ocr_can_be_retried_and_persists_the_result(): void
    {
        Storage::fake('tribunal')->put('tribunal_documents/complaint.pdf', 'document');
        $admin = User::factory()->administrator()->create();
        $admin->assignRole('administrator');
        $case = TribunalCase::factory()->create([
            'is_formal_charge' => true,
            'document_path' => 'tribunal_documents/complaint.pdf',
            'document_disk' => 'tribunal',
            'searchable_text' => null,
        ]);

        $ocrService = Mockery::mock(ComplaintLetterOcrService::class);
        $ocrService->shouldReceive('extractText')->once()->andThrow(new RuntimeException('OCR engine unavailable.'));
        $ocrService->shouldReceive('extractText')->once()->andReturn('Text extracted after retry.');
        $this->app->instance(ComplaintLetterOcrService::class, $ocrService);

        Livewire::actingAs($admin)
            ->test(CaseOcrText::class, ['case' => $case])
            ->assertSee('OCR engine unavailable.')
            ->assertSee('Retry / Run OCR')
            ->call('retryOcr')
            ->assertSee('Text extracted after retry.')
            ->assertDontSee('Retry / Run OCR');

        $this->assertDatabaseHas('tribunal_cases', [
            'id' => $case->id,
            'searchable_text' => 'Text extracted after retry.',
        ]);
    }
}
