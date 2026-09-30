<?php

namespace Tests\Feature;

use App\Livewire\Admin\UserManagement;
use App\Models\IncidentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'administrator']);
        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
    }

    public function test_admin_can_delete_user_with_no_incident_reports(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(UserManagement::class)
            ->call('deleteUser', $user->id);

        $this->assertSoftDeleted($user);
    }

    public function test_delete_is_blocked_when_user_is_student_in_incident_report(): void
    {
        $student = User::factory()->create();
        $reporter = User::factory()->create();

        IncidentReport::factory()->create([
            'student_id' => $student->id,
            'reporter_id' => $reporter->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(UserManagement::class)
            ->call('deleteUser', $student->id);

        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }

    public function test_delete_is_blocked_when_user_is_reporter_in_incident_report(): void
    {
        $student = User::factory()->create();
        $reporter = User::factory()->create();

        IncidentReport::factory()->create([
            'student_id' => $student->id,
            'reporter_id' => $reporter->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(UserManagement::class)
            ->call('deleteUser', $reporter->id);

        $this->assertDatabaseHas('users', ['id' => $reporter->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserManagement::class)
            ->call('deleteUser', $this->admin->id);

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_edit_staff_account(): void
    {
        $staff = User::factory()->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $staff->assignRole('staff');

        Livewire::actingAs($this->admin)
            ->test(UserManagement::class)
            ->call('openEditModal', $staff->id)
            ->set('firstName', 'Updated')
            ->set('lastName', 'Staff')
            ->set('email', 'updated@example.com')
            ->set('password', 'new-password')
            ->set('role', 'administrator')
            ->call('updateUser');

        $staff->refresh();

        $this->assertSame('Updated Staff', $staff->name);
        $this->assertSame('updated@example.com', $staff->email);
        $this->assertTrue(Hash::check('new-password', $staff->password));
        $this->assertTrue($staff->hasRole('administrator'));
        $this->assertDatabaseHas('auth_audit_logs', ['event_type' => 'user_updated']);
    }

    public function test_admin_cannot_remove_own_administrator_role(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserManagement::class)
            ->call('openEditModal', $this->admin->id)
            ->set('role', 'staff')
            ->call('updateUser');

        $this->assertTrue($this->admin->fresh()->hasRole('administrator'));
    }
}
