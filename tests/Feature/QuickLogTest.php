<?php

namespace Tests\Feature;

use App\Livewire\Staff\QuickLog;
use App\Livewire\Staff\StudentLookup;
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

    public function test_name_selection_populates_quick_log_student_identity(): void
    {
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $student = User::factory()->student()->create([
            'name' => 'Juan Dela Cruz',
            'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'section' => 'C',
        ]);
        $student->assignRole('student');

        Livewire::test(StudentLookup::class)
            ->set('studentName', 'Juan Dela')
            ->assertSee('Juan Dela Cruz')
            ->assertSee($student->student_id)
            ->call('selectStudent', $student->id)
            ->assertSet('studentId', $student->student_id)
            ->assertDispatched('staff-student-selected');

        Livewire::test(QuickLog::class)
            ->dispatch('staff-student-selected', userId: $student->id)
            ->assertSet('studentId', $student->student_id)
            ->assertSet('studentName', 'Juan Dela Cruz')
            ->assertSet('studentCollege', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->assertSet('studentProgram', 'BSIT')
            ->assertSet('studentYearLevel', '4th Year')
            ->assertSet('studentSection', 'C')
            ->assertSet('studentVerified', true);
    }

    public function test_duplicate_name_suggestions_can_be_narrowed_by_college_and_program(): void
    {
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $first = User::factory()->student()->create([
            'name' => 'Juan Dela Cruz',
            'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            'program' => 'BSIT',
        ]);
        $first->assignRole('student');

        $second = User::factory()->student()->create([
            'name' => 'Juan Dela Cruz',
            'college' => 'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY',
            'program' => 'BSBA',
        ]);
        $second->assignRole('student');

        Livewire::test(StudentLookup::class)
            ->set('studentName', 'Juan Dela Cruz')
            ->assertSee($first->student_id)
            ->assertSee($second->student_id)
            ->set('collegeFilter', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('programFilter', 'BSIT')
            ->assertSee($first->student_id)
            ->assertDontSee($second->student_id);
    }

    public function test_entering_student_id_in_lookup_populates_name_and_academic_details(): void
    {
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $student = User::factory()->student()->create([
            'name' => 'Juan Dela Cruz',
            'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            'program' => 'BSIT',
            'year_level' => '4th Year',
            'section' => 'C',
        ]);
        $student->assignRole('student');

        Livewire::test(StudentLookup::class)
            ->set('studentId', $student->student_id)
            ->assertSet('studentName', 'Juan Dela Cruz')
            ->assertSet('collegeFilter', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->assertSet('programFilter', 'BSIT')
            ->assertSet('studentResolved', true)
            ->assertDispatched('staff-student-selected');
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
