<?php

namespace Tests\Feature;

use App\Enums\CaseStatus;
use App\Jobs\ProcessDocumentOcr;
use App\Livewire\Admin\UserManagement;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\TribunalCase;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TribunalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_tribunal_panel_can_access_the_module_and_is_redirected_to_it_from_dashboard(): void
    {
        $panelMember = $this->createTribunalPanel();

        $this->assertTrue($panelMember->canAccessTribunalModule());

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('dashboard'))
            ->assertRedirect(route('tribunal.cases.index'));

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->get(route('tribunal.cases.index'))
            ->assertOk()
            ->assertSee('Tribunal Module')
            ->assertSee('+ New Case')
            ->assertDontSee('Dashboard Overview')
            ->assertDontSee('Staff & User Management');
    }

    public function test_users_outside_the_tribunal_panel_cannot_access_the_module(): void
    {
        $staffMember = User::factory()->staff()->create();
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');

        $this->withSession(['mfa_verified' => true])
            ->actingAs($staffMember)
            ->get(route('tribunal.cases.index'))
            ->assertForbidden();

        $this->withSession(['mfa_verified' => true])
            ->actingAs($administrator)
            ->get(route('tribunal.cases.index'))
            ->assertForbidden();
    }

    public function test_tribunal_panel_can_create_a_case_with_reference_module_details(): void
    {
        $panelMember = $this->createTribunalPanel();

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
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
        ]);
    }

    public function test_case_creation_rejects_invalid_status_values(): void
    {
        $panelMember = $this->createTribunalPanel();

        $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
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

    public function test_tribunal_panel_can_upload_view_and_transition_a_case_document(): void
    {
        Storage::fake('tribunal');
        Queue::fake();

        $panelMember = $this->createTribunalPanel();

        $response = $this->withSession(['mfa_verified' => true])
            ->actingAs($panelMember)
            ->postJson(route('tribunal.documents.upload'), [
                'document' => UploadedFile::fake()->createWithContent('signed-resolution.pdf', '%PDF-1.4 test'),
                'title' => 'Signed Resolution',
                'description' => 'Final tribunal findings.',
            ]);

        $response->assertAccepted();

        $case = TribunalCase::query()->findOrFail($response->json('case_id'));

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

    public function test_administrator_can_provision_a_tribunal_panel_user(): void
    {
        $this->seed(RoleSeeder::class);
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');

        Livewire::actingAs($administrator)
            ->test(UserManagement::class)
            ->call('openCreateModal')
            ->assertSee('Tribunal Panel')
            ->set('firstName', 'Tribunal')
            ->set('lastName', 'Member')
            ->set('email', 'tribunal.member@example.test')
            ->set('password', 'secure-password-123')
            ->set('role', 'tribunal')
            ->call('createUser')
            ->assertHasNoErrors();

        $panelMember = User::query()->where('email', 'tribunal.member@example.test')->firstOrFail();

        $this->assertSame('tribunal_panel', $panelMember->role_type);
        $this->assertTrue($panelMember->canAccessTribunalModule());
        $this->assertTrue($panelMember->can('access tribunal module'));

        Livewire::actingAs($administrator)
            ->test(UserManagement::class)
            ->call('openEditModal', $panelMember->id)
            ->set('role', 'staff')
            ->call('updateUser')
            ->assertHasNoErrors();

        $this->assertSame('osdw_staff', $panelMember->refresh()->role_type);
        $this->assertFalse($panelMember->canAccessTribunalModule());
    }

    private function createTribunalPanel(): User
    {
        $permission = Permission::firstOrCreate([
            'name' => 'access tribunal module',
            'guard_name' => 'web',
        ]);
        $role = Role::firstOrCreate([
            'name' => 'tribunal',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo($permission);

        $panelMember = User::factory()->create(['role_type' => 'tribunal_panel']);
        $panelMember->assignRole($role);

        return $panelMember;
    }
}
