<?php

namespace Tests\Feature;

use App\Livewire\Staff\QuickLog;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuickLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_formatted_student_number_is_verified_and_saved_as_user_foreign_key(): void
    {
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $staff = User::factory()->staff()->create();
        $staff->assignRole('staff');

        $student = User::factory()->student()->create(['student_id' => '24-10000']);
        $student->assignRole('student');

        $offense = OffenseRule::factory()->minor()->create();

        $component = Livewire::actingAs($staff)
            ->test(QuickLog::class)
            ->set('studentId', '24-10000')
            ->assertSet('studentVerified', true)
            ->set('offenseId', $offense->id)
            ->set('description', 'Student arrived without the required identification card.')
            ->call('submit');

        $component->assertHasNoErrors();
        $component->assertDispatched('incident-logged');

        $report = IncidentReport::where('report_type', 'Quick Log')->firstOrFail();

        $this->assertSame($student->id, $report->student_id);
        $this->assertSame($offense->id, $report->offense_id);
        $this->assertDatabaseCount('tribunal_cases', 0);
    }

    public function test_handbook_search_suggests_minor_violations_and_routes_major_violations_to_formal_charge(): void
    {
        $minor = OffenseRule::factory()->minor()->create([
            'code' => 'PR-101',
            'title' => 'No ID',
        ]);
        $major = OffenseRule::factory()->create([
            'code' => 'AC-201',
            'title' => 'Academic Dishonesty',
            'severity_level' => 'Major',
            'gravity' => 'major',
        ]);

        $component = Livewire::test(QuickLog::class)
            ->set('violationSearch', 'No ID')
            ->assertSee('Minor Infraction')
            ->assertSee('No ID')
            ->assertSet('offenseId', (string) $minor->id);

        $component->call('selectOffenseSuggestion', $major->id)
            ->assertRedirect(route('staff.formal-charge', ['offense' => $major->id]));
    }

    public function test_staff_incident_report_form_renders_live_handbook_search_with_severity_suggestions(): void
    {
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff = User::factory()->staff()->create();
        $staff->assignRole('staff');

        OffenseRule::factory()->minor()->create([
            'code' => 'PR-101',
            'title' => 'No ID',
        ]);
        OffenseRule::factory()->create([
            'code' => 'AC-201',
            'title' => 'Academic Dishonesty',
            'severity_level' => 'Major',
            'gravity' => 'major',
        ]);

        $this->actingAs($staff)
            ->get(route('staff.report.create'))
            ->assertOk()
            ->assertSee('Search Handbook Violations')
            ->assertSee('PR-101')
            ->assertSee('AC-201')
            ->assertSee('function incidentReportForm', false)
            ->assertSee('Major Formal Charge');
    }
}
