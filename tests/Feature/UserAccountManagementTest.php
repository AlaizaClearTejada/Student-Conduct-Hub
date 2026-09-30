<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::firstOrCreate(['name' => 'administrator']);
        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);
    }

    public function test_admin_can_suspend_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $student = User::factory()->create();
        $student->assignRole('student');

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/users/{$student->id}/suspend", [
                'reason' => 'Violation of policy',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User suspended successfully',
            ]);

        $this->assertNotNull($student->fresh()->suspended_at);
        $this->assertEquals('Violation of policy', $student->fresh()->suspension_reason);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'users',
            'entity_id' => $student->id,
            'action' => 'SUSPEND',
            'actor_id' => $admin->id,
        ]);
    }

    public function test_admin_can_soft_delete_and_restore_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $student = User::factory()->create();
        $student->assignRole('student');

        // Soft Delete
        $deleteResponse = $this->actingAs($admin)->deleteJson("/api/v1/users/{$student->id}");
        $deleteResponse->assertStatus(200);

        $this->assertSoftDeleted($student);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'users',
            'entity_id' => $student->id,
            'action' => 'DELETE',
            'actor_id' => $admin->id,
        ]);

        // Restore
        $restoreResponse = $this->actingAs($admin)->postJson("/api/v1/users/{$student->id}/restore");
        $restoreResponse->assertStatus(200);

        $this->assertNotSoftDeleted($student);

        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'users',
            'entity_id' => $student->id,
            'action' => 'RESTORE',
            'actor_id' => $admin->id,
        ]);
    }
}
