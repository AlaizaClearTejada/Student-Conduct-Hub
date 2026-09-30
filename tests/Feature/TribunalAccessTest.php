<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Jobs\ProcessDocumentOcr;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\TribunalCase;
use App\Models\User;
use App\Models\ViolationRecord;
use App\Services\TribunalCaseRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TribunalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_tribunal_module_and_see_navigation_link(): void
    {
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');

        $this->assertTrue($administrator->canAccessTribunalModule());

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tribunal Module');

        $this->get(route('tribunal.cases.index'))->assertOk();
    }

    public function test_tribunal_panel_member_can_access_tribunal_module(): void
    {
        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);
        $this->assertTrue($panelMember->canAccessTribunalModule());
        $this->assertSame('/dashboard/tribunal/cases', parse_url(route('tribunal.cases.index'), PHP_URL_PATH));

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee('Tribunal Module');
    }

    public function test_ordinary_staff_cannot_access_tribunal_module(): void
    {
        $staffMember = User::factory()->staff()->create();

        $this->actingAs($staffMember)
            ->get(route('tribunal.cases.index'))
            ->assertForbidden();
    }

    public function test_case_number_generator_skips_numbers_already_used_by_tribunal_cases(): void
    {
        $prefix = 'CSU-'.now()->format('Y-m').'-';

        TribunalCase::factory()->create(['case_number' => $prefix.'0001']);
        TribunalCase::factory()->create(['case_number' => $prefix.'0002']);

        $this->assertSame($prefix.'0003', ViolationRecord::generateCaseTrackingNumber());
    }

    public function test_router_reuses_a_case_linked_to_either_source(): void
    {
        Queue::fake();

        $reporter = User::factory()->create();
        $student = User::factory()->create();
        $offense = OffenseRule::factory()->create(['requires_tribunal' => true]);
        $report = IncidentReport::factory()->submitted()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => $offense->id,
        ]);
        $existingCase = TribunalCase::factory()->create([
            'incident_report_id' => $report->id,
        ]);

        $routedCase = app(TribunalCaseRouter::class)->routeIncidentReport($report, dispatchOcr: false);

        $this->assertSame($existingCase->id, $routedCase->id);
        $this->assertDatabaseCount('tribunal_cases', 1);
    }

    public function test_tribunal_panel_can_upload_scanned_document_for_ocr(): void
    {
        Storage::fake('tribunal');
        Queue::fake();

        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);

        $response = $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->post(route('tribunal.documents.upload'), [
                'document' => UploadedFile::fake()->create('signed-resolution.pdf', 20_480, 'application/pdf'),
                'title' => 'Signed Resolution',
                'description' => 'Final tribunal findings and sanctions.',
            ]);

        $response->assertStatus(202);

        $case = TribunalCase::findOrFail($response->json('case_id'));

        $this->assertMatchesRegularExpression('/^CSU-\d{4}-\d{2}-\d{4}$/', $case->case_number);
        $this->assertSame('Signed Resolution', $case->title);
        $this->assertSame('tribunal', $case->document_disk);
        Storage::disk('tribunal')->assertExists($case->document_path);
        $this->assertStringStartsWith('tribunal_documents/', $case->document_path);
        Queue::assertPushed(ProcessDocumentOcr::class);
    }

    public function test_active_list_includes_major_and_severe_reports_but_excludes_minor_and_moderate(): void
    {
        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);
        $majorOffense = OffenseRule::factory()->create([
            'severity_level' => 'Major',
            'gravity' => 'minor',
        ]);
        $severeOffense = OffenseRule::factory()->create([
            'severity_level' => 'Severe',
            'gravity' => 'major',
            'requires_tribunal' => false,
        ]);
        $severeOffense = OffenseRule::factory()->create([
            'severity_level' => 'Severe',
            'gravity' => 'major',
            'requires_tribunal' => false,
        ]);
        $minorOffense = OffenseRule::factory()->minor()->create([
            'severity_level' => 'Minor',
            'gravity' => 'major',
            'requires_tribunal' => true,
        ]);
        $moderateOffense = OffenseRule::factory()->create([
            'severity_level' => 'Moderate',
            'gravity' => 'major',
            'requires_tribunal' => true,
        ]);
        $majorReport = IncidentReport::factory()->create([
            'reporter_id' => $panelMember->id,
            'student_id' => $panelMember->id,
            'offense_id' => $majorOffense->id,
            'status' => 'Submitted',
        ]);
        $severeReport = IncidentReport::factory()->create([
            'reporter_id' => $panelMember->id,
            'student_id' => $panelMember->id,
            'offense_id' => $severeOffense->id,
            'status' => 'Submitted',
        ]);
        $minorReport = IncidentReport::factory()->create([
            'reporter_id' => $panelMember->id,
            'student_id' => $panelMember->id,
            'offense_id' => $minorOffense->id,
            'status' => 'Submitted',
        ]);
        $moderateReport = IncidentReport::factory()->create([
            'reporter_id' => $panelMember->id,
            'student_id' => $panelMember->id,
            'offense_id' => $moderateOffense->id,
            'status' => 'Submitted',
        ]);
        $majorCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-1001',
            'incident_report_id' => $majorReport->id,
        ]);
        $severeCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-1004',
            'incident_report_id' => $severeReport->id,
        ]);
        TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-1002',
            'incident_report_id' => $minorReport->id,
        ]);
        TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-1003',
            'incident_report_id' => $moderateReport->id,
        ]);

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee($majorCase->case_number)
            ->assertSee($severeCase->case_number)
            ->assertDontSee('CSU-2026-09-1002')
            ->assertDontSee('CSU-2026-09-1003');
    }

    public function test_active_list_excludes_non_major_violation_records_even_when_assigned_to_tribunal(): void
    {
        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);
        $majorOffense = OffenseRule::factory()->create([
            'severity_level' => 'Major',
            'gravity' => 'minor',
            'requires_tribunal' => false,
        ]);
        $minorOffense = OffenseRule::factory()->minor()->create([
            'severity_level' => 'Minor',
            'gravity' => 'major',
            'requires_tribunal' => true,
        ]);
        $moderateOffense = OffenseRule::factory()->create([
            'severity_level' => 'Moderate',
            'gravity' => 'major',
            'requires_tribunal' => true,
        ]);

        $majorRecord = ViolationRecord::factory()->create([
            'offense_id' => $majorOffense->id,
            'investigation_type' => 'Tribunal',
            'status' => 'Pending Review',
        ]);
        $minorRecord = ViolationRecord::factory()->create([
            'offense_id' => $minorOffense->id,
            'investigation_type' => 'Tribunal',
            'status' => 'Pending Review',
        ]);
        $moderateRecord = ViolationRecord::factory()->create([
            'offense_id' => $moderateOffense->id,
            'assigned_to_sdt' => true,
            'status' => 'Pending Review',
        ]);

        $majorCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-2001',
            'violation_record_id' => $majorRecord->id,
        ]);
        TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-2002',
            'violation_record_id' => $minorRecord->id,
        ]);
        TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-2003',
            'violation_record_id' => $moderateRecord->id,
        ]);

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee($majorCase->case_number)
            ->assertDontSee('CSU-2026-09-2002')
            ->assertDontSee('CSU-2026-09-2003');
    }

    public function test_index_links_only_unlinked_major_or_severe_violation_records(): void
    {
        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);
        $majorOffense = OffenseRule::factory()->create(['severity_level' => 'Major']);
        $severeOffense = OffenseRule::factory()->create([
            'severity_level' => 'Severe',
            'gravity' => 'minor',
            'requires_tribunal' => false,
        ]);
        $minorOffense = OffenseRule::factory()->minor()->create([
            'severity_level' => 'Minor',
            'gravity' => 'major',
            'requires_tribunal' => true,
        ]);
        $majorRecord = ViolationRecord::factory()->create([
            'case_tracking_number' => 'CSU-2026-09-3001',
            'offense_id' => $majorOffense->id,
            'investigation_type' => 'Summary',
            'assigned_to_sdt' => false,
            'status' => 'Pending Review',
        ]);
        $severeRecord = ViolationRecord::factory()->create([
            'case_tracking_number' => 'CSU-2026-09-3003',
            'offense_id' => $severeOffense->id,
            'investigation_type' => 'Summary',
            'assigned_to_sdt' => false,
            'status' => 'Pending Review',
        ]);
        $minorRecord = ViolationRecord::factory()->create([
            'case_tracking_number' => 'CSU-2026-09-3002',
            'offense_id' => $minorOffense->id,
            'investigation_type' => 'Tribunal',
            'status' => 'Pending Review',
        ]);

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee($majorRecord->case_tracking_number)
            ->assertSee($severeRecord->case_tracking_number)
            ->assertDontSee($minorRecord->case_tracking_number);

        $this->assertDatabaseHas('tribunal_cases', [
            'violation_record_id' => $majorRecord->id,
            'case_number' => $majorRecord->case_tracking_number,
        ]);
        $this->assertDatabaseHas('tribunal_cases', [
            'violation_record_id' => $severeRecord->id,
            'case_number' => $severeRecord->case_tracking_number,
        ]);
        $this->assertDatabaseMissing('tribunal_cases', [
            'violation_record_id' => $minorRecord->id,
        ]);
    }

    public function test_index_creates_a_tribunal_case_for_an_unlinked_major_report(): void
    {
        Queue::fake();

        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);
        $offense = OffenseRule::factory()->create([
            'severity_level' => 'Major',
            'gravity' => 'minor',
            'requires_tribunal' => true,
        ]);
        $report = IncidentReport::factory()->submitted()->create([
            'reporter_id' => $panelMember->id,
            'student_id' => $panelMember->id,
            'offense_id' => $offense->id,
            'evidence_path' => 'confidential_evidence/tribunal-resolution.pdf',
        ]);

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee($offense->title);

        $case = TribunalCase::where('incident_report_id', $report->id)->firstOrFail();

        $this->assertSame($report->description, $case->description);
        $this->assertSame($offense->title, $case->title);
        Queue::assertNothingPushed();
    }

    public function test_active_cases_open_review_view_and_support_resolution_drafted_transition(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('case-evidence/review.pdf', '%PDF-1.4 test document');

        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);
        $activeCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-2026-09-0001',
            'title' => 'Disciplinary Violation Review',
            'description' => 'Test disciplinary incident description.',
            'searchable_text' => 'Tribunal findings and recommended sanctions.',
            'document_path' => 'case-evidence/review.pdf',
            'status' => CaseStatus::UNDER_FORMAL_INVESTIGATION,
            'incident_report_id' => IncidentReport::factory()->create([
                'reporter_id' => $panelMember->id,
                'student_id' => $panelMember->id,
                'offense_id' => OffenseRule::factory()->create([
                    'severity_level' => 'Major',
                    'gravity' => 'minor',
                    'requires_tribunal' => false,
                ])->id,
                'status' => 'Submitted',
            ])->id,
        ]);
        $resolvedCase = TribunalCase::factory()->create([
            'status' => CaseStatus::CASE_RESOLVED,
        ]);

        $this->assertFalse(Route::has('tribunal.cases.create'));
        $this->assertFalse(Route::has('tribunal.cases.store'));

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertDontSee('+ New Case')
            ->assertSee($activeCase->case_number)
            ->assertDontSee($resolvedCase->case_number)
            ->assertSee(route('tribunal.cases.show', $activeCase), false);

        $this->get(route('tribunal.cases.show', $activeCase))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Tribunal Cases')
            ->assertSee('Review Case')
            ->assertSee($activeCase->case_number)
            ->assertSee($activeCase->title)
            ->assertSee($activeCase->description)
            ->assertSee('OCR Extracted Text (Searchable)')
            ->assertSee('Tribunal findings and recommended sanctions.')
            ->assertSee('Original Scanned Document')
            ->assertSee(route('tribunal.cases.document', $activeCase), false)
            ->assertSee('Update Pipeline Status')
            ->assertSee('value="resolution_drafted"', false)
            ->assertSee('Transition State');

        $this->assertStringContainsString('grid-cols-1 md:grid-cols-2', $this->get(route('tribunal.cases.show', $activeCase))->getContent());

        $this->get(route('tribunal.cases.document', $activeCase))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->patch(route('tribunal.cases.updateStatus', $activeCase), [
            'status' => CaseStatus::RESOLUTION_DRAFTED->value,
        ])->assertRedirect();

        $this->assertSame(CaseStatus::RESOLUTION_DRAFTED, $activeCase->refresh()->status);
    }

    public function test_pipeline_rejects_skipped_transitions(): void
    {
        $case = TribunalCase::factory()->create([
            'status' => CaseStatus::UNDER_INFORMAL_DISCUSSION,
        ]);

        $this->expectException(\Exception::class);

        $case->transitionTo(CaseStatus::RESOLUTION_DRAFTED);
    }
}
