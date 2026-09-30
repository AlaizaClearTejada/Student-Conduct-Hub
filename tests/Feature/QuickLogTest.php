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
    }
}
