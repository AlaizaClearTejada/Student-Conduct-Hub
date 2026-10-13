<?php

namespace Tests\Feature;

use App\Livewire\Admin\CaseManagement;
use App\Livewire\Staff\FormalChargeForm;
use App\Livewire\Staff\QuickLog;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
use App\Models\ViolationRecord;
use App\Services\OffenseProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OffenseProgressionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['administrator', 'staff', 'student'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->admin = User::factory()->administrator()->create();
        $this->admin->assignRole('administrator');

        $this->staff = User::factory()->staff()->create();
        $this->staff->assignRole('staff');

        $this->student = User::factory()->student()->create([
            'student_id' => '25-12345',
        ]);
        $this->student->assignRole('student');
    }

    public function test_repeat_cases_increment_the_offense_count_and_assign_each_sanction_tier(): void
    {
        $offense = OffenseRule::factory()->minor()->create([
            'first_offense_sanction' => 'Written warning',
            'second_offense_sanction' => 'Counseling',
            'third_offense_sanction' => 'Probation',
            'standard_sanction' => 'Standard action',
        ]);
        $expectedSanctions = [
            1 => 'Written warning',
            2 => 'Counseling',
            3 => 'Probation',
            4 => 'Probation',
        ];

        foreach ($expectedSanctions as $expectedCount => $expectedSanction) {
            $report = IncidentReport::factory()->submitted()->create([
                'reporter_id' => $this->staff->id,
                'student_id' => $this->student->id,
                'offense_id' => $offense->id,
                'report_type' => 'Quick Log',
            ]);

            Livewire::actingAs($this->admin)
                ->test(CaseManagement::class)
                ->call('openAcceptModal', $report->id)
                ->call('acceptReport')
                ->assertHasNoErrors();

            $this->assertDatabaseHas('violation_records', [
                'student_id' => $this->student->id,
                'offense_id' => $offense->id,
                'offense_count' => $expectedCount,
                'applied_sanction' => $expectedSanction,
            ]);
        }
    }

    public function test_student_dashboard_displays_active_category_counts_and_progression(): void
    {
        $offense = OffenseRule::factory()->minor()->create([
            'second_offense_sanction' => 'Counseling',
        ]);
        ViolationRecord::factory()->active()->create([
            'student_id' => $this->student->id,
            'offense_id' => $offense->id,
            'reported_by' => $this->staff->id,
            'offense_count' => 2,
            'applied_sanction' => 'Counseling',
        ]);

        $this->actingAs($this->student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('My Conduct & Offense Tracker')
            ->assertSee($offense->category)
            ->assertSee('2nd Offense')
            ->assertSee('Counseling');
    }

    public function test_staff_quick_log_previews_history_and_the_next_minor_sanction(): void
    {
        $offense = OffenseRule::factory()->minor()->create([
            'first_offense_sanction' => 'Written warning',
            'second_offense_sanction' => 'Counseling',
        ]);
        ViolationRecord::factory()->create([
            'student_id' => $this->student->id,
            'offense_id' => $offense->id,
            'reported_by' => $this->staff->id,
            'offense_count' => 1,
            'applied_sanction' => 'Written warning',
        ]);

        Livewire::actingAs($this->staff)
            ->test(QuickLog::class)
            ->set('studentId', $this->student->student_id)
            ->set('offenseId', (string) $offense->id)
            ->assertSet('offenseCount', 2)
            ->assertSee('2nd Offense')
            ->assertSee('Counseling')
            ->assertSee('Prior Offense History');
    }

    public function test_staff_formal_charge_previews_history_for_the_selected_major_offense(): void
    {
        $offense = OffenseRule::factory()->create([
            'severity_level' => 'Major',
            'gravity' => 'major',
            'requires_tribunal' => true,
            'first_offense_sanction' => 'Formal warning',
            'second_offense_sanction' => 'Suspension',
        ]);
        ViolationRecord::factory()->create([
            'student_id' => $this->student->id,
            'offense_id' => $offense->id,
            'reported_by' => $this->staff->id,
            'offense_count' => 1,
            'applied_sanction' => 'Formal warning',
        ]);

        Livewire::actingAs($this->staff)
            ->test(FormalChargeForm::class)
            ->set('studentIdInput', $this->student->student_id)
            ->set('offenseId', (string) $offense->id)
            ->assertSet('offenseCount', 2)
            ->assertSee('2nd Offense')
            ->assertSee('Suspension')
            ->assertSee('Prior Offense History');
    }

    public function test_approved_appeal_updates_the_sanction_and_adjusts_future_progression(): void
    {
        $offense = OffenseRule::factory()->minor()->create([
            'first_offense_sanction' => 'Written warning',
            'second_offense_sanction' => 'Counseling',
        ]);
        ViolationRecord::factory()->create([
            'student_id' => $this->student->id,
            'offense_id' => $offense->id,
            'reported_by' => $this->staff->id,
            'offense_count' => 1,
        ]);
        $appealedCase = ViolationRecord::factory()->appealed()->create([
            'student_id' => $this->student->id,
            'offense_id' => $offense->id,
            'reported_by' => $this->staff->id,
            'offense_count' => 2,
            'applied_sanction' => 'Counseling',
        ]);

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->set('activeTab', 'cases')
            ->call('viewCase', $appealedCase->id)
            ->set('appealOffenseCount', 1)
            ->set('appealDecisionNotes', 'Evidence supports reducing this case to a first offense.')
            ->call('approveAppeal')
            ->assertHasNoErrors()
            ->assertSee('Appeal Approved');

        $appealedCase->refresh();
        $this->assertSame('Appealed', $appealedCase->status);
        $this->assertSame('Approved', $appealedCase->appeal_decision);
        $this->assertSame(1, $appealedCase->offense_count);
        $this->assertSame(-1, $appealedCase->offense_count_adjustment);
        $this->assertSame('Written warning', $appealedCase->applied_sanction);
        $this->assertNotNull($appealedCase->appeal_decided_at);

        $this->assertSame(
            2,
            app(OffenseProgressionService::class)->nextCount($this->student->id, $offense->id)
        );

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->set('activeTab', 'cases')
            ->assertSee('Offense History Across Students')
            ->assertSee($this->student->student_id)
            ->assertSee('Written warning');
    }

    public function test_staff_can_retrieve_history_for_the_static_incident_form(): void
    {
        $offense = OffenseRule::factory()->minor()->create([
            'first_offense_sanction' => 'Written warning',
            'second_offense_sanction' => 'Counseling',
        ]);
        ViolationRecord::factory()->create([
            'student_id' => $this->student->id,
            'offense_id' => $offense->id,
            'reported_by' => $this->staff->id,
            'offense_count' => 1,
        ]);

        $response = $this->actingAs($this->staff)
            ->getJson(route('staff.offense-history', [
                'student_id' => $this->student->id,
                'offense_id' => $offense->id,
            ]));

        $response->assertOk()
            ->assertJsonPath('next_count', 2)
            ->assertJsonPath('next_level', '2nd Offense')
            ->assertJsonPath('next_sanction', 'Counseling')
            ->assertJsonCount(1, 'records');
    }
}
