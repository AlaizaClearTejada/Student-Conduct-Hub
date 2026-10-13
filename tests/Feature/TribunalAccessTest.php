<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Jobs\ProcessDocumentOcr;
use App\Livewire\Admin\CaseManagement;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\TribunalCase;
use App\Models\User;
use App\Models\ViolationRecord;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TribunalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_access_the_module_and_is_redirected_to_admin_dashboard(): void
    {
        $administrator = $this->createAdministrator();

        $this->assertTrue($administrator->canAccessTribunalModule());

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee('Tribunal Module')
            ->assertSee('+ New Case')
            ->assertSee('bg-inferno text-white', false);
    }

    public function test_users_without_tribunal_permission_cannot_access_the_module(): void
    {
        $staffMember = User::factory()->staff()->create();

        $this->withSession(['mfa_verified' => true])
            ->actingAs($staffMember)
            ->get(route('tribunal.cases.index'))
            ->assertForbidden();
    }

    public function test_administrators_with_the_module_permission_see_and_can_open_the_tribunal_sidebar_link(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('tribunal.cases.index'), false)
            ->assertSee('Tribunal Module');

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee(route('tribunal.cases.index'), false)
            ->assertSee('Tribunal Module');

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee(route('tribunal.cases.index'), false)
            ->assertSee('bg-inferno text-white', false);
    }

    public function test_administrator_can_create_a_case_with_reference_module_details(): void
    {
        $administrator = $this->createAdministrator();

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('tribunal.cases.create'))
            ->assertOk()
            ->assertSee('File a New Tribunal Case');

        $this->post(route('tribunal.cases.store'), [
            'case_number' => 'TR-2026-001',
            'title' => 'Academic integrity review',
            'respondent_name' => 'Sample Student',
            'incident_date' => '2026-10-01',
            'category' => 'Academic Dishonesty',
            'priority' => 'high',
            'description' => 'Review the submitted evidence.',
            'status' => CaseStatus::UNDER_INFORMAL_DISCUSSION->value,
        ])
            ->assertRedirect(route('tribunal.cases.index'));

        $this->assertDatabaseHas('tribunal_cases', [
            'case_number' => 'TR-2026-001',
            'respondent_name' => 'Sample Student',
            'priority' => 'high',
            'status' => CaseStatus::UNDER_INFORMAL_DISCUSSION->value,
            'is_formal_charge' => true,
        ]);
    }

    public function test_case_creation_rejects_invalid_status_values(): void
    {
        $administrator = $this->createAdministrator();

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->post(route('tribunal.cases.store'), [
                'case_number' => 'TR-2026-INVALID',
                'title' => 'Invalid case',
                'priority' => 'normal',
                'status' => 'not_a_case_status',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('tribunal_cases', [
            'case_number' => 'TR-2026-INVALID',
        ]);
    }

    public function test_administrator_can_upload_view_and_transition_a_case_document(): void
    {
        Storage::fake('tribunal');
        Queue::fake();

        $administrator = $this->createAdministrator();
        $report = IncidentReport::factory()->formalCharge()->create([
            'reporter_id' => $administrator->id,
            'student_id' => User::factory()->student()->create()->id,
            'offense_id' => OffenseRule::factory()->minor()->create()->id,
        ]);
        $case = TribunalCase::factory()->create([
            'incident_report_id' => $report->id,
        ]);

        $response = $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->postJson(route('tribunal.documents.upload'), [
                'document' => UploadedFile::fake()->createWithContent('signed-resolution.pdf', '%PDF-1.4 test'),
                'case_id' => $case->id,
            ]);

        $response->assertAccepted();

        $this->assertSame($case->id, $response->json('case_id'));
        $case->refresh();

        $this->assertSame('tribunal', $case->document_disk);
        Storage::disk('tribunal')->assertExists($case->document_path);
        Queue::assertPushed(ProcessDocumentOcr::class);

        $this->get(route('tribunal.cases.show', $case))
            ->assertOk()
            ->assertSee($case->case_number)
            ->assertSee('Original Scanned Document')
            ->assertSee(route('tribunal.cases.document', $case), false);

        $this->get(route('tribunal.cases.document', $case))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->patch(route('tribunal.cases.updateStatus', $case), [
            'status' => CaseStatus::UNDER_FORMAL_INVESTIGATION->value,
        ])->assertRedirect();

        $this->assertSame(CaseStatus::UNDER_FORMAL_INVESTIGATION, $case->refresh()->status);

        $this->patch(route('tribunal.cases.updateStatus', $case), [
            'status' => CaseStatus::CASE_RESOLVED->value,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame(CaseStatus::UNDER_FORMAL_INVESTIGATION, $case->refresh()->status);
    }

    public function test_tribunal_lists_only_major_cases_and_formal_charges_and_blocks_minor_case_routes(): void
    {
        $administrator = $this->createAdministrator();
        $student = User::factory()->student()->create();
        $reporter = User::factory()->staff()->create();
        $minorOffense = OffenseRule::factory()->minor()->create();
        $majorOffense = OffenseRule::factory()->create(['severity_level' => 'Major']);
        $severeOffense = OffenseRule::factory()->severe()->create();

        $minorReport = IncidentReport::factory()->quickLog()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => $minorOffense->id,
        ]);
        $majorReport = IncidentReport::factory()->quickLog()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => $majorOffense->id,
        ]);
        $formalCharge = IncidentReport::factory()->formalCharge()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => $minorOffense->id,
        ]);
        $majorSeverityReport = IncidentReport::factory()->quickLog()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => $minorOffense->id,
            'severity' => 'Major',
        ]);
        $severeQuickLog = IncidentReport::factory()->quickLog()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => $severeOffense->id,
        ]);

        $minorCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-MINOR-0001',
            'incident_report_id' => $minorReport->id,
        ]);
        $majorCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-MAJOR-0001',
            'incident_report_id' => $majorReport->id,
        ]);
        $formalCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-FORMAL-0001',
            'incident_report_id' => $formalCharge->id,
        ]);
        $majorSeverityCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-MAJOR-SEVERITY-0001',
            'incident_report_id' => $majorSeverityReport->id,
        ]);
        $severeCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-SEVERE-0001',
            'incident_report_id' => $severeQuickLog->id,
        ]);
        $unclassifiedCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-UNCLASSIFIED-0001',
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
        $majorViolationCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-MAJOR-RECORD-0001',
            'violation_record_id' => $majorRecord->id,
        ]);
        $minorViolationCase = TribunalCase::factory()->create([
            'case_number' => 'CSU-MINOR-RECORD-0001',
            'violation_record_id' => $minorRecord->id,
        ]);

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee($majorCase->case_number)
            ->assertSee($majorViolationCase->case_number)
            ->assertSee($majorSeverityCase->case_number)
            ->assertSee($formalCase->case_number)
            ->assertDontSee($minorCase->case_number)
            ->assertDontSee($minorViolationCase->case_number)
            ->assertDontSee($severeCase->case_number)
            ->assertDontSee($unclassifiedCase->case_number);

        $this->get(route('tribunal.cases.show', $minorCase))->assertNotFound();
        $this->get(route('tribunal.cases.document', $minorCase))->assertNotFound();
        $this->patch(route('tribunal.cases.updateStatus', $minorCase), [
            'status' => CaseStatus::UNDER_FORMAL_INVESTIGATION->value,
        ])->assertNotFound();

        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');

        Livewire::actingAs($administrator)
            ->test(Dashboard::class)
            ->assertSet('pendingCasesCount', 1)
            ->assertDontSee($minorRecord->case_tracking_number);

        Livewire::actingAs($administrator)
            ->test(CaseManagement::class)
            ->call('setTab', 'cases')
            ->set('filterInvestigationType', 'Tribunal')
            ->assertSee($majorRecord->case_tracking_number)
            ->assertDontSee($minorRecord->case_tracking_number);
    }

    public function test_document_upload_cannot_create_or_attach_a_minor_case(): void
    {
        Storage::fake('tribunal');
        Queue::fake();

        $administrator = $this->createAdministrator();
        $student = User::factory()->student()->create();
        $reporter = User::factory()->staff()->create();
        $minorReport = IncidentReport::factory()->quickLog()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => OffenseRule::factory()->minor()->create()->id,
        ]);
        $minorCase = TribunalCase::factory()->create([
            'incident_report_id' => $minorReport->id,
        ]);

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->postJson(route('tribunal.documents.upload'), [
                'document' => UploadedFile::fake()->createWithContent('minor-evidence.pdf', '%PDF-1.4 test'),
                'case_id' => $minorCase->id,
            ])
            ->assertNotFound();

        $this->assertNull($minorCase->refresh()->document_path);
        Queue::assertNothingPushed();
    }

    public function test_admin_cannot_assign_a_minor_violation_to_the_tribunal(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');
        $minorRecord = ViolationRecord::factory()->create([
            'offense_id' => OffenseRule::factory()->minor()->create()->id,
            'investigation_type' => 'Summary',
            'assigned_to_sdt' => false,
        ]);

        Livewire::actingAs($administrator)
            ->test(CaseManagement::class)
            ->call('openAssignSDTModal', $minorRecord->id)
            ->set('selectedSDTMembers', ['member-1', 'member-2', 'member-3', 'member-4', 'member-5'])
            ->call('assignSDT')
            ->assertHasErrors('selectedSDTMembers');

        $this->assertFalse($minorRecord->refresh()->assigned_to_sdt);
        $this->assertDatabaseMissing('tribunal_cases', [
            'case_number' => $minorRecord->case_tracking_number,
        ]);
    }

    public function test_minor_quick_log_cannot_be_accepted_as_a_tribunal_case(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');
        $student = User::factory()->student()->create();
        $reporter = User::factory()->staff()->create();
        $minorReport = IncidentReport::factory()->quickLog()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => OffenseRule::factory()->minor()->create()->id,
        ]);

        Livewire::actingAs($administrator)
            ->test(CaseManagement::class)
            ->call('openAcceptModal', $minorReport->id)
            ->set('acceptInvestigationType', 'Tribunal')
            ->call('acceptReport')
            ->assertHasErrors('acceptInvestigationType');

        $this->assertDatabaseMissing('violation_records', [
            'student_id' => $student->id,
            'offense_id' => $minorReport->offense_id,
        ]);
        $this->assertDatabaseMissing('tribunal_cases', [
            'incident_report_id' => $minorReport->id,
        ]);
    }

    public function test_tribunal_case_relationships_reach_the_student_and_offense_models(): void
    {
        $student = User::factory()->create(['role_type' => 'student']);
        $reporter = User::factory()->create(['role_type' => 'staff']);
        $offense = OffenseRule::factory()->create();
        $report = IncidentReport::factory()->create([
            'reporter_id' => $reporter->id,
            'student_id' => $student->id,
            'offense_id' => $offense->id,
        ]);
        $case = TribunalCase::factory()->create([
            'incident_report_id' => $report->id,
        ]);

        $this->assertSame($student->id, $case->incidentReport->student->id);
        $this->assertSame($offense->id, $case->incidentReport->offense->id);
    }

    public function test_user_management_does_not_offer_a_separate_tribunal_role(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = $this->createAdministrator();
        $this->assertDatabaseMissing('roles', ['name' => 'tribunal']);

        Livewire::actingAs($administrator)
            ->test(UserManagement::class)
            ->call('openCreateModal')
            ->assertSee('<option value="staff">Staff</option>', false)
            ->assertSee('<option value="administrator">Administrator</option>', false)
            ->assertDontSee('Tribunal Panel');
    }

    public function test_legacy_tribunal_users_are_promoted_to_administrators_by_the_migration(): void
    {
        $legacyRole = Role::create([
            'name' => 'tribunal',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create(['role_type' => 'tribunal_panel']);
        $user->assignRole($legacyRole);

        $migration = require database_path(
            'migrations/2026_10_13_202958_remove_tribunal_panel_role_and_grant_access_to_administrators.php'
        );
        $migration->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = $user->fresh();

        $this->assertSame('admin', $user->role_type);
        $this->assertTrue($user->hasRole('administrator'));
        $this->assertTrue($user->can('access tribunal module'));
        $this->assertDatabaseMissing('roles', ['id' => $legacyRole->id]);
        $this->assertDatabaseMissing('model_has_roles', ['role_id' => $legacyRole->id]);
    }

    private function createAdministrator(): User
    {
        $this->seed(RoleSeeder::class);

        $permission = Permission::firstOrCreate([
            'name' => 'access tribunal module',
            'guard_name' => 'web',
        ]);
        $role = Role::firstOrCreate([
            'name' => 'administrator',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permission);

        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole($role);

        return $administrator;
    }
}
