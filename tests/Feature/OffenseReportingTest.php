<?php

namespace Tests\Feature;

use App\Models\Offense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OffenseReportingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'administrator']);
        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);
    }

    public function test_staff_can_file_offense_report()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $student = User::factory()->create();
        $student->assignRole('student');

        $payload = [
            'student_id' => $student->id,
            'offense_type' => 'MAJOR',
            'offense_category' => 'ACADEMIC_DISHONESTY',
            'incident_date' => now()->format('Y-m-d'),
            'incident_time' => '14:30',
            'location' => 'Library',
            'description' => 'Student was caught plagiarizing the final essay assignment. The content matches exactly with an online source.',
        ];

        $response = $this->actingAs($staff)->postJson('/api/v1/offenses', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('offense.status', 'SUBMITTED')
            ->assertJsonPath('offense.offense_type', 'MAJOR');

        $this->assertDatabaseHas('offenses', [
            'student_id' => $student->id,
            'filed_by' => $staff->id,
            'offense_category' => 'ACADEMIC_DISHONESTY',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'offenses',
            'action' => 'CREATE',
            'actor_id' => $staff->id,
        ]);
    }

    public function test_staff_can_upload_digital_evidence()
    {
        Storage::fake('local');

        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $student = User::factory()->create();
        $student->assignRole('student');

        $offense = Offense::create([
            'student_id' => $student->id,
            'filed_by' => $staff->id,
            'case_number' => 'TEST-001',
            'offense_type' => 'MINOR',
            'offense_category' => 'CONDUCT_VIOLATION',
            'incident_date' => now()->format('Y-m-d'),
            'incident_time' => '10:00',
            'location' => 'Hallway',
            'description' => 'Loud disturbance during class hours. Testing upload evidence feature.',
            'status' => 'SUBMITTED',
        ]);

        $file = UploadedFile::fake()->image('evidence.jpg');

        $response = $this->actingAs($staff)
            ->postJson("/api/v1/offenses/{$offense->id}/evidence", [
                'file' => $file,
            ]);

        $response->assertStatus(201);

        Storage::disk('local')->assertExists('evidence/'.$file->hashName());

        $this->assertDatabaseHas('digital_evidence', [
            'offense_id' => $offense->id,
            'file_name' => 'evidence.jpg',
            'uploaded_by' => $staff->id,
        ]);
    }
}
