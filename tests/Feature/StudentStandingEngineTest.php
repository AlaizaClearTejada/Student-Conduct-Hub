<?php

namespace Tests\Feature;

use App\Models\Offense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentStandingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'administrator']);
        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);
    }

    public function test_engine_evaluates_good_standing_for_no_offenses()
    {
        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($student)->getJson("/api/v1/students/{$student->id}/standing");

        $response->assertStatus(200)
            ->assertJsonPath('standing.standing_status', 'GOOD')
            ->assertJsonPath('standing.active_cases_count', 0);
    }

    public function test_engine_evaluates_review_standing_for_active_offense()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $student = User::factory()->create();
        $student->assignRole('student');

        $offense = Offense::create([
            'student_id' => $student->id,
            'filed_by' => $staff->id,
            'case_number' => 'TEST-002',
            'offense_type' => 'MINOR',
            'offense_category' => 'ATTENDANCE',
            'incident_date' => now()->format('Y-m-d'),
            'incident_time' => '10:00',
            'location' => 'Classroom',
            'description' => 'Unexcused absence.',
            'status' => 'DRAFT',
        ]);

        // Trigger update to SUBMITTED
        $this->actingAs($staff)->patchJson("/api/v1/offenses/{$offense->id}", [
            'status' => 'SUBMITTED',
        ]);

        // Engine runs synchronously on event listener. Let's check standing.
        $response = $this->actingAs($staff)->getJson("/api/v1/students/{$student->id}/standing");

        $response->assertStatus(200)
            ->assertJsonPath('standing.standing_status', 'REVIEW')
            ->assertJsonPath('standing.active_cases_count', 1);
    }

    public function test_engine_evaluates_probation_for_major_resolved_offense()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $student = User::factory()->create();
        $student->assignRole('student');

        $offense = Offense::create([
            'student_id' => $student->id,
            'filed_by' => $staff->id,
            'case_number' => 'TEST-003',
            'offense_type' => 'MAJOR',
            'offense_category' => 'ACADEMIC_DISHONESTY',
            'incident_date' => now()->format('Y-m-d'),
            'incident_time' => '10:00',
            'location' => 'Classroom',
            'description' => 'Plagiarism.',
            'status' => 'SUBMITTED',
        ]);

        // Resolve the offense
        $this->actingAs($staff)->patchJson("/api/v1/offenses/{$offense->id}", [
            'status' => 'RESOLVED',
            'sanction_details' => ['active' => true, 'type' => 'PROBATION'],
        ]);

        $response = $this->actingAs($staff)->getJson("/api/v1/students/{$student->id}/standing");

        $response->assertStatus(200)
            ->assertJsonPath('standing.standing_status', 'PROBATION')
            ->assertJsonPath('standing.major_offenses_count', 1);

        // Check clearance hold
        $holdResponse = $this->actingAs($student)->getJson("/api/v1/students/{$student->id}/clearance-hold");
        $holdResponse->assertStatus(200)
            ->assertJsonPath('has_hold', true);
    }
}
