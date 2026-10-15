<?php

namespace Tests\Feature;

use App\Livewire\Staff\FormalChargeForm;
use App\Livewire\Staff\QuickLog;
use App\Livewire\Staff\SmartIncidentAssistance;
use App\Models\OffenseRule;
use App\Models\User;
use App\Services\ComplaintLetterOcrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmartIncidentAssistanceTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->staff = User::factory()->staff()->create();
        $this->staff->assignRole('staff');
        $this->student = User::factory()->student()->create([
            'student_id' => '24-10000',
        ]);
        $this->student->assignRole('student');
    }

    public function test_dashboard_places_smart_assistance_above_manual_report_options(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.dashboard'));

        $response->assertOk()
            ->assertSeeLivewire(SmartIncidentAssistance::class)
            ->assertSee('Smart Incident Assistance')
            ->assertSee('Upload Complaint Letter Image')
            ->assertSee('Log Minor Infraction')
            ->assertSee('File Formal Charge');

        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Log Minor Infraction'),
            strpos($html, 'Smart Incident Assistance')
        );
    }

    public function test_search_recommendations_display_major_and_minor_severity_tags(): void
    {
        $minor = OffenseRule::factory()->minor()->create([
            'code' => 'PR-101',
            'title' => 'No ID',
        ]);
        OffenseRule::factory()->create([
            'code' => 'AC-201',
            'title' => 'Academic Dishonesty',
            'severity_level' => 'Major',
            'gravity' => 'major',
        ]);

        Livewire::actingAs($this->staff)
            ->test(SmartIncidentAssistance::class)
            ->set('search', 'No ID')
            ->assertSee('[MINOR]')
            ->assertSee('PR-101')
            ->set('search', 'Academic Dishonesty')
            ->assertSee('[MAJOR]')
            ->call('selectOffense', $minor->id)
            ->assertDispatched('open-quick-log-modal');
    }

    public function test_ocr_minor_intake_prefills_student_complainant_and_description_in_quick_log(): void
    {
        $offense = OffenseRule::factory()->minor()->create([
            'code' => 'PR-101',
            'title' => 'No ID',
        ]);
        $ocrText = "Complainant: Jane Doe\nStudent ID: {$this->student->student_id}\nIncident: Student entered campus without an ID card.";
        $ocr = \Mockery::mock(ComplaintLetterOcrService::class)->makePartial();
        $ocr->shouldReceive('extractText')->once()->andReturn($ocrText);
        app()->instance(ComplaintLetterOcrService::class, $ocr);

        Livewire::actingAs($this->staff)
            ->test(SmartIncidentAssistance::class)
            ->set('complaintLetter', UploadedFile::fake()->image('complaint.png'))
            ->assertSet('extractedStudentId', $this->student->student_id)
            ->assertSet('extractedComplainant', 'Jane Doe')
            ->assertSet('suggestedOffenseId', $offense->id)
            ->assertSee('[MINOR]')
            ->call('continueWithSuggestion')
            ->assertDispatched('open-quick-log-modal');

        Livewire::actingAs($this->staff)
            ->test(QuickLog::class)
            ->dispatch('open-quick-log-modal', offenseId: $offense->id, studentId: $this->student->student_id, complainant: 'Jane Doe', description: $ocrText)
            ->assertSet('showModal', true)
            ->assertSet('studentVerified', true)
            ->assertSet('offenseId', (string) $offense->id)
            ->assertSet('complainantName', 'Jane Doe')
            ->assertSet('description', $ocrText)
            ->call('submit');

        $this->assertDatabaseHas('incident_reports', [
            'student_id' => $this->student->id,
            'offense_id' => $offense->id,
            'complainant_name' => 'Jane Doe',
            'description' => $ocrText,
        ]);
    }

    public function test_ocr_distinguishes_the_reporting_party_from_the_respondent_student(): void
    {
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $respondent = User::factory()->student()->create([
            'name' => 'Mike Paet',
            'student_id' => '24-10005',
        ]);
        $respondent->assignRole('student');

        $offense = OffenseRule::factory()->minor()->create([
            'code' => 'PR-101',
            'title' => 'No ID',
        ]);
        $ocrText = 'I, Alaiza Clear Tejada (Student ID: 24-00006), report that I witnessed the student, Mike Paet (Student ID: 24-10005).';
        $ocr = \Mockery::mock(ComplaintLetterOcrService::class)->makePartial();
        $ocr->shouldReceive('extractText')->once()->andReturn($ocrText);
        app()->instance(ComplaintLetterOcrService::class, $ocr);

        Livewire::actingAs($this->staff)
            ->test(SmartIncidentAssistance::class)
            ->set('complaintLetter', UploadedFile::fake()->image('complaint.png'))
            ->assertSet('extractedStudentId', '24-10005')
            ->assertSet('extractedComplainant', 'Alaiza Clear Tejada')
            ->call('continueWithSuggestion')
            ->assertDispatched('open-quick-log-modal', function (string $event, array $params) use ($respondent): bool {
                return $params['studentId'] === $respondent->student_id
                    && $params['complainant'] === 'Alaiza Clear Tejada';
            });
    }

    public function test_ocr_uses_the_signature_as_complainant_when_no_i_statement_is_present(): void
    {
        $ocrService = app(ComplaintLetterOcrService::class);
        $letter = "Please take action against Mike Paet (Student ID: 24-10005).\n\nSincerely,\nAlaiza Clear Tejada (Student ID: 24-00006)";

        $this->assertSame('24-10005', $ocrService->extractRespondentStudentId($letter));
        $this->assertSame('Alaiza Clear Tejada', $ocrService->extractComplainantName($letter));
    }

    public function test_ocr_major_intake_redirects_to_prefilled_form_without_exposing_text_in_url(): void
    {
        $offense = OffenseRule::factory()->create([
            'code' => 'AC-201',
            'title' => 'Academic Dishonesty',
            'severity_level' => 'Major',
            'gravity' => 'major',
        ]);
        $ocrText = "Complainant: Jane Doe\nStudent ID: {$this->student->student_id}\nIncident: Academic dishonesty was reported.";
        $ocr = \Mockery::mock(ComplaintLetterOcrService::class)->makePartial();
        $ocr->shouldReceive('extractText')->once()->andReturn($ocrText);
        app()->instance(ComplaintLetterOcrService::class, $ocr);

        Livewire::actingAs($this->staff)
            ->test(SmartIncidentAssistance::class)
            ->set('complaintLetter', UploadedFile::fake()->image('complaint.png'))
            ->assertSet('suggestedOffenseId', $offense->id)
            ->call('continueWithSuggestion')
            ->assertRedirect(route('staff.formal-charge', ['offense' => $offense->id]));

        $this->assertSame([
            'student_id' => $this->student->student_id,
            'complainant' => 'Jane Doe',
            'description' => $ocrText,
        ], session('smart_incident_intake'));

        $this->get(route('staff.formal-charge', ['offense' => $offense->id]))
            ->assertOk()
            ->assertSee($this->student->student_id)
            ->assertSee('Jane Doe')
            ->assertSee('AC-201 — Academic Dishonesty');

        session()->put('smart_incident_intake', [
            'student_id' => $this->student->student_id,
            'complainant' => 'Jane Doe',
            'description' => $ocrText,
        ]);

        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->assertSet('studentIdInput', $this->student->student_id)
            ->assertSet('studentVerified', true)
            ->assertSet('program', $this->student->program)
            ->assertSee('Student selected:')
            ->assertSet('complainantName', 'Jane Doe')
            ->assertSet('incidentDescription', $ocrText);
    }
}
