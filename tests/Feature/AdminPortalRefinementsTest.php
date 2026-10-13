<?php

namespace Tests\Feature;

use App\Livewire\Admin\AuditLogs;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Models\AuthAuditLog;
use App\Models\User;
use App\Models\ViolationRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPortalRefinementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logs_can_be_filtered_by_assigned_user_role(): void
    {
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');
        $staff = User::factory()->staff()->create();
        $staff->assignRole('staff');
        $student = User::factory()->student()->create();
        $student->assignRole('student');

        $this->createAuditLog($administrator, 'admin-audit-marker');
        $this->createAuditLog($staff, 'staff-audit-marker');
        $this->createAuditLog($student, 'student-audit-marker');

        Livewire::actingAs($administrator)
            ->test(AuditLogs::class)
            ->assertSee('Filter by User Role')
            ->assertSee('Administrator')
            ->assertSee('Staff')
            ->assertSee('Student')
            ->set('roleFilter', 'staff')
            ->assertSee('staff-audit-marker')
            ->assertDontSee('admin-audit-marker')
            ->assertDontSee('student-audit-marker')
            ->call('clearFilters')
            ->assertSee('admin-audit-marker')
            ->assertSee('student-audit-marker');
    }

    public function test_user_management_includes_student_role_filter_and_displays_all_results_without_pagination(): void
    {
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');
        $student = User::factory()->student()->create(['email' => 'filtered-student@example.test']);
        $student->assignRole('student');

        $component = Livewire::actingAs($administrator)
            ->test(UserManagement::class)
            ->assertSee('<option value="student">Student</option>', false)
            ->set('roleFilter', 'student')
            ->assertSee('filtered-student@example.test')
            ->assertDontSee($administrator->email);

        for ($index = 1; $index <= 12; $index++) {
            $staff = User::factory()->staff()->create([
                'email' => "staff-{$index}@example.test",
            ]);
            $staff->assignRole('staff');
        }

        $component->set('roleFilter', 'staff')
            ->assertSee('staff-12@example.test')
            ->assertDontSee('pagination');
    }

    public function test_dashboard_stats_are_database_counts_and_alerts_are_removed(): void
    {
        $administrator = User::factory()->administrator()->create();
        $administrator->assignRole('administrator');

        for ($index = 1; $index <= 2; $index++) {
            $staff = User::factory()->staff()->create();
            $staff->assignRole('staff');
        }

        User::factory()->staff()->create(['suspended_at' => now()]);
        ViolationRecord::factory()->pending()->create(['investigation_type' => 'Summary']);
        ViolationRecord::factory()->resolved()->create();

        Livewire::actingAs($administrator)
            ->test(Dashboard::class)
            ->assertSet('activeStaffCount', 2)
            ->assertSet('suspendedAccountsCount', 1)
            ->assertSet('pendingCasesCount', 1)
            ->assertSet('resolvedCasesCount', 1)
            ->assertSee('Cases Resolved')
            ->assertDontSee('System Health')
            ->assertDontSee('Security Alerts');
    }

    private function createAuditLog(User $user, string $ipAddress): void
    {
        AuthAuditLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $ipAddress,
            'event_type' => 'login',
        ]);
    }
}
