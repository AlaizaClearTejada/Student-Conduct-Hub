<?php

namespace Tests\Feature;

use App\Models\OffenseRule;
use App\Models\User;
use App\Models\ViolationRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentPortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that students can only access their own violation records (data isolation).
     */
    public function test_student_can_only_view_own_records(): void
    {
        // Create roles
        $studentRole = Role::findOrCreate('student');

        // Create two students
        $student1 = User::factory()->create();
        $student1->assignRole('student');

        $student2 = User::factory()->create();
        $student2->assignRole('student');

        // Create staff member
        $staff = User::factory()->create();

        // Create offense rules
        $offense1 = OffenseRule::factory()->create(['code' => 'TEST-001']);
        $offense2 = OffenseRule::factory()->create(['code' => 'TEST-002']);

        // Create violation records for both students
        $violation1 = ViolationRecord::factory()->create([
            'student_id' => $student1->id,
            'offense_id' => $offense1->id,
            'reported_by' => $staff->id,
        ]);

        $violation2 = ViolationRecord::factory()->create([
            'student_id' => $student2->id,
            'offense_id' => $offense2->id,
            'reported_by' => $staff->id,
        ]);

        // Student 1 logs in and views dashboard
        $response = $this->actingAs($student1)->get(route('student.dashboard'));

        $response->assertStatus(200);
        // Should see their own violation's offense code
        $response->assertSee('TEST-001');
        // Should NOT see student 2's violation's offense code
        $response->assertDontSee('TEST-002');
    }

    /**
     * Test IDOR protection: students cannot view other students' records via URL manipulation.
     */
    public function test_student_cannot_access_other_student_record_via_url_manipulation(): void
    {
        // Create roles
        $studentRole = Role::findOrCreate('student');

        // Create two students
        $student1 = User::factory()->create();
        $student1->assignRole('student');

        $student2 = User::factory()->create();
        $student2->assignRole('student');

        // Create staff member
        $staff = User::factory()->create();

        // Create offense rule
        $offense = OffenseRule::factory()->create();

        // Create violation for student 2
        $violation = ViolationRecord::factory()->create([
            'student_id' => $student2->id,
            'offense_id' => $offense->id,
            'reported_by' => $staff->id,
        ]);

        // Student 1 tries to access Student 2's record by changing URL
        $response = $this->actingAs($student1)->get(route('student.records.show', $violation));

        // Should be denied (403 Forbidden)
        $response->assertStatus(403);
    }

    /**
     * Test that guests cannot access the student dashboard.
     */
    public function test_guest_cannot_access_student_dashboard(): void
    {
        $response = $this->get(route('student.dashboard'));

        // Should redirect to login
        $response->assertRedirect(route('login'));
    }

    /**
     * Test that non-student users cannot access student routes.
     */
    public function test_non_student_users_cannot_access_student_routes(): void
    {
        // Create roles
        Role::findOrCreate('student');
        $adminRole = Role::findOrCreate('administrator');

        // Create admin user (not a student)
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        // Try to access student dashboard as admin
        $response = $this->withSession(['mfa_verified' => true])
            ->actingAs($admin)
            ->get(route('student.dashboard'));

        // Should be denied (Spatie Permission middleware blocks non-students)
        $response->assertStatus(403);
    }

    /**
     * Test that staff members can view student records via admin routes (policy allows).
     * Note: Staff would access records through admin routes, not student routes.
     * This tests the policy authorization, not route access.
     */
    public function test_staff_can_view_student_records_via_policy(): void
    {
        // Create roles
        Role::findOrCreate('student');
        $staffRole = Role::findOrCreate('staff');

        // Create student and staff
        $student = User::factory()->create();
        $student->assignRole('student');

        $staff = User::factory()->create();
        $staff->assignRole('staff');

        // Create offense and violation
        $offense = OffenseRule::factory()->create();
        $violation = ViolationRecord::factory()->create([
            'student_id' => $student->id,
            'offense_id' => $offense->id,
            'reported_by' => $staff->id,
        ]);

        // Test that the policy allows staff to view the record
        $canView = $staff->can('view', $violation);

        $this->assertTrue($canView, 'Staff should be able to view student violation records via policy');
    }

    /**
     * Test that the controller uses eager loading to prevent N+1 queries.
     */
    public function test_dashboard_uses_eager_loading_to_prevent_n_plus_one_queries(): void
    {
        // Create roles
        $studentRole = Role::findOrCreate('student');

        // Create student
        $student = User::factory()->create();
        $student->assignRole('student');

        // Create staff member
        $staff = User::factory()->create();

        // Create offense rules and violations
        $offenses = OffenseRule::factory()->count(3)->create();

        foreach ($offenses as $offense) {
            ViolationRecord::factory()->create([
                'student_id' => $student->id,
                'offense_id' => $offense->id,
                'reported_by' => $staff->id,
            ]);
        }

        // Enable query log
        DB::enableQueryLog();

        // Access dashboard
        $this->actingAs($student)->get(route('student.dashboard'));

        // Get all queries
        $queries = DB::getQueryLog();

        // Count queries that fetch violation records
        $violationQueries = collect($queries)->filter(function ($query) {
            return str_contains($query['query'], 'violation_records');
        })->count();

        // Should only have ONE query for violations (with eager loading)
        // If N+1 exists, there would be 1 + 3 = 4 queries (1 for list + 3 for relationships)
        $this->assertLessThanOrEqual(2, $violationQueries, 'N+1 query problem detected');
    }

    /**
     * Test that students have read-only access (cannot create violations).
     */
    public function test_students_cannot_create_violation_records(): void
    {
        // Create roles
        $studentRole = Role::findOrCreate('student');

        // Create student
        $student = User::factory()->create();
        $student->assignRole('student');

        // Create offense
        $offense = OffenseRule::factory()->create();

        // Try to create violation (student should not be able to)
        $canCreate = $student->can('create', ViolationRecord::class);

        $this->assertFalse($canCreate, 'Students should not be able to create violation records');
    }

    /**
     * Test that students cannot update violation records.
     */
    public function test_students_cannot_update_violation_records(): void
    {
        // Create roles
        $studentRole = Role::findOrCreate('student');

        // Create student
        $student = User::factory()->create();
        $student->assignRole('student');

        // Create staff member
        $staff = User::factory()->create();

        // Create offense and violation
        $offense = OffenseRule::factory()->create();
        $violation = ViolationRecord::factory()->create([
            'student_id' => $student->id,
            'offense_id' => $offense->id,
            'reported_by' => $staff->id,
        ]);

        // Try to update violation (student should not be able to)
        $canUpdate = $student->can('update', $violation);

        $this->assertFalse($canUpdate, 'Students should have read-only access to their records');
    }

    /**
     * Test that students cannot delete violation records.
     */
    public function test_students_cannot_delete_violation_records(): void
    {
        // Create roles
        $studentRole = Role::findOrCreate('student');

        // Create student
        $student = User::factory()->create();
        $student->assignRole('student');

        // Create staff member
        $staff = User::factory()->create();

        // Create offense and violation
        $offense = OffenseRule::factory()->create();
        $violation = ViolationRecord::factory()->create([
            'student_id' => $student->id,
            'offense_id' => $offense->id,
            'reported_by' => $staff->id,
        ]);

        // Try to delete violation (student should not be able to)
        $canDelete = $student->can('delete', $violation);

        $this->assertFalse($canDelete, 'Students should not be able to delete institutional records');
    }

    /**
     * Test that dashboard calculates conduct standing correctly.
     */
    public function test_dashboard_correctly_calculates_conduct_standing(): void
    {
        // Create roles
        $studentRole = Role::findOrCreate('student');

        // Create student with good standing (no active sanctions)
        $goodStudent = User::factory()->create();
        $goodStudent->assignRole('student');

        // Create student with active sanction
        $activeStudent = User::factory()->create();
        $activeStudent->assignRole('student');

        // Create staff member
        $staff = User::factory()->create();

        // Create offense
        $offense = OffenseRule::factory()->create();

        // Create resolved violation for good student
        ViolationRecord::factory()->resolved()->create([
            'student_id' => $goodStudent->id,
            'offense_id' => $offense->id,
            'reported_by' => $staff->id,
        ]);

        // Create active sanction for active student
        ViolationRecord::factory()->active()->create([
            'student_id' => $activeStudent->id,
            'offense_id' => $offense->id,
            'reported_by' => $staff->id,
        ]);

        // Check good student's standing
        $response = $this->actingAs($goodStudent)->get(route('student.dashboard'));
        $response->assertSee('Good Standing');

        // Check active student's standing
        $response = $this->actingAs($activeStudent)->get(route('student.dashboard'));
        $response->assertSee('Action Required');
    }

    public function test_dashboard_counts_granular_statuses_in_their_summary_groups(): void
    {
        Role::findOrCreate('student');

        $student = User::factory()->create();
        $student->assignRole('student');
        $staff = User::factory()->create();
        $offense = OffenseRule::factory()->create();

        foreach (['Pending Review', 'Notice Sent', 'Sanction Active', 'Appealed', 'Resolved'] as $status) {
            ViolationRecord::factory()->create([
                'student_id' => $student->id,
                'offense_id' => $offense->id,
                'reported_by' => $staff->id,
                'status' => $status,
            ]);
        }

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertViewHas('pendingReviewCount', 2);
        $response->assertViewHas('activeSanctionsCount', 1);
        $response->assertViewHas('appealedCount', 1);
        $response->assertViewHas('resolvedCount', 1);
        $response->assertSee('bg-yellow-100 text-yellow-800 border-yellow-300');
        $response->assertSee('bg-amber-100 text-amber-800 border-amber-300');
        $response->assertSee('bg-red-100 text-red-800 border-red-300');
        $response->assertSee('bg-blue-100 text-blue-800 border-blue-300');
        $response->assertSee('bg-green-100 text-green-800 border-green-300');

        $viewData = $response->viewData();
        $viewData['violationRecords']->first()->status = 'Unmapped Status';
        $renderedDashboard = view('student.dashboard', $viewData)->render();

        $this->assertStringContainsString('bg-gray-100 text-gray-800 border-gray-300', $renderedDashboard);
    }
}
