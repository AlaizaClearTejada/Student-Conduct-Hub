<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentOcr;
use App\Livewire\Staff\FormalChargeForm;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\TribunalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FormalChargeFormTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $student;

    private OffenseRule $offense;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->student = User::factory()->student()->create([
            'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            'year_level' => '2nd Year',
            'section' => 'A',
        ]);
        $this->student->assignRole('student');

        $this->offense = OffenseRule::factory()->create([
            'severity_level' => 'Major',
            'gravity' => 'minor',
            'is_active' => true,
        ]);
    }

    // ─── Route Authorization ────────────────────────────────────────────────

    public function test_staff_can_access_formal_charge_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.formal-charge'));

        $response->assertStatus(200);
    }

    public function test_student_cannot_access_formal_charge_page(): void
    {
        $response = $this->actingAs($this->student)->get(route('staff.formal-charge'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_formal_charge_page(): void
    {
        $response = $this->get(route('staff.formal-charge'));

        $response->assertRedirect(route('login'));
    }

    // ─── Auto-Fetch Student Data ────────────────────────────────────────────

    public function test_entering_valid_student_id_populates_fields(): void
    {
        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('studentIdInput', $this->student->student_id)
            ->assertSet('studentVerified', true)
            ->assertSet('studentDbId', $this->student->id)
            ->assertSet('studentName', $this->student->name)
            ->assertSet('email', $this->student->email)
            ->assertSet('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->assertSet('yearLevel', '2nd Year')
            ->assertSet('section', 'A');
    }

    public function test_entering_invalid_student_id_does_not_populate_fields(): void
    {
        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('studentIdInput', 'INVALID-9999')
            ->assertSet('studentVerified', false)
            ->assertSet('studentDbId', null)
            ->assertSet('email', '');
    }

    // ─── Successful Submission ──────────────────────────────────────────────

    public function test_staff_can_submit_formal_charge_successfully(): void
    {
        Storage::fake('local');
        Queue::fake();

        $file = UploadedFile::fake()->create('evidence.pdf', 512, 'application/pdf');

        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('studentIdInput', $this->student->student_id)
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('yearLevel', '2nd Year')
            ->set('section', 'A')
            ->set('email', $this->student->email)
            ->set('offenseId', $this->offense->id)
            ->set('incidentDescription', 'The student was caught submitting a plagiarized research paper during the final exam week.')
            ->set('evidenceFile', $file)
            ->set('certification', true)
            ->call('submitCharge')
            ->assertRedirect(route('staff.dashboard'));

        $this->assertDatabaseHas('incident_reports', [
            'reporter_id' => $this->staff->id,
            'student_id' => $this->student->id,
            'offense_id' => $this->offense->id,
            'report_type' => 'Formal Charge',
            'status' => 'Submitted',
        ]);

        $report = IncidentReport::where('reporter_id', $this->staff->id)->first();
        $this->assertStringStartsWith('INC-', $report->tracking_number);
        $this->assertStringStartsWith('confidential_evidence/', $report->evidence_path);

        $tribunalCase = TribunalCase::where('incident_report_id', $report->id)->firstOrFail();
        $this->assertSame($report->evidence_path, $tribunalCase->document_path);
        $this->assertSame($report->description, $tribunalCase->description);
        Queue::assertPushed(ProcessDocumentOcr::class);
    }

    // ─── Validation Failures ────────────────────────────────────────────────

    public function test_submission_fails_without_student_id(): void
    {
        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('offenseId', $this->offense->id)
            ->set('incidentDescription', 'A detailed incident description for testing validation.')
            ->set('certification', true)
            ->call('submitCharge')
            ->assertHasErrors(['studentIdInput', 'studentDbId']);
    }

    public function test_submission_fails_without_evidence(): void
    {
        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('studentIdInput', $this->student->student_id)
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('yearLevel', '2nd Year')
            ->set('section', 'A')
            ->set('email', $this->student->email)
            ->set('offenseId', $this->offense->id)
            ->set('incidentDescription', 'A detailed incident description for testing validation.')
            ->set('certification', true)
            ->call('submitCharge')
            ->assertHasErrors(['evidenceFile']);
    }

    public function test_submission_fails_without_certification(): void
    {
        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('studentIdInput', $this->student->student_id)
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('yearLevel', '2nd Year')
            ->set('section', 'A')
            ->set('email', $this->student->email)
            ->set('offenseId', $this->offense->id)
            ->set('incidentDescription', 'A detailed incident description for testing validation.')
            ->set('evidenceFile', UploadedFile::fake()->create('evidence.pdf', 512, 'application/pdf'))
            ->set('certification', false)
            ->call('submitCharge')
            ->assertHasErrors(['certification']);
    }

    public function test_description_must_be_at_least_twenty_characters(): void
    {
        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('studentIdInput', $this->student->student_id)
            ->set('incidentDescription', 'Too short.')
            ->call('submitCharge')
            ->assertHasErrors(['incidentDescription']);
    }

    public function test_major_offenses_are_loaded_on_mount(): void
    {
        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->assertSet('majorOffenses', fn ($offenses) => $offenses->contains($this->offense));
    }
}
