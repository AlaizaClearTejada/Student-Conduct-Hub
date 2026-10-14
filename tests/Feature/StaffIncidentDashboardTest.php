<?php

namespace Tests\Feature;

use App\Models\IncidentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffIncidentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_incident_hub_metrics_use_live_report_counts_scoped_to_current_staff(): void
    {
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $staff = User::factory()->staff()->create();
        $staff->assignRole('staff');
        $otherStaff = User::factory()->staff()->create();
        $otherStaff->assignRole('staff');

        IncidentReport::factory()->submitted()->count(2)->create(['reporter_id' => $staff->id]);
        IncidentReport::factory()->underReview()->create(['reporter_id' => $staff->id]);
        IncidentReport::factory()->resolved()->create(['reporter_id' => $staff->id]);
        IncidentReport::factory()->dismissed()->create(['reporter_id' => $staff->id]);
        IncidentReport::factory()->submitted()->create(['reporter_id' => $otherStaff->id]);

        $this->actingAs($staff)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Total Reports')
            ->assertSee('Submitted')
            ->assertSee('Under Review')
            ->assertSee('Resolved')
            ->assertSee('>5</p>', false)
            ->assertSee('>2</p>', false)
            ->assertSee('>1</p>', false);
    }
}
