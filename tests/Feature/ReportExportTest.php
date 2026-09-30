<?php

namespace Tests\Feature;

use App\Exports\CaseSummaryExport;
use App\Exports\MonthlyReportExport;
use App\Livewire\Admin\CaseManagement;
use App\Models\IncidentReport;
use App\Models\OffenseRule;
use App\Models\TribunalCase;
use App\Models\User;
use App\Models\ViolationRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $student;

    private OffenseRule $offense;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'administrator']);
        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->student = User::factory()->student()->create();
        $this->student->assignRole('student');

        $this->offense = OffenseRule::factory()->create();
    }

    public function test_admin_can_access_case_management_page(): void
    {
        $response = $this->withSession(['mfa_verified' => true])
            ->actingAs($this->admin)
            ->get(route('admin.cases'));

        $response
            ->assertStatus(200)
            ->assertSee('/livewire/livewire.js', false);
    }

    public function test_case_management_shows_report_tabs(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->assertSee('Cases')
            ->assertSee('Case Summary')
            ->assertSee('Monthly Report')
            ->assertSee('By Type');
    }

    public function test_accepting_a_major_report_links_its_violation_record_to_the_tribunal_case(): void
    {
        $majorOffense = OffenseRule::factory()->create([
            'severity_level' => 'Major',
            'gravity' => 'minor',
        ]);
        $report = IncidentReport::factory()->submitted()->create([
            'reporter_id' => $this->staff->id,
            'student_id' => $this->student->id,
            'offense_id' => $majorOffense->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('openAcceptModal', $report->id)
            ->assertSet('acceptInvestigationType', 'Tribunal')
            ->call('acceptReport');

        $record = ViolationRecord::where('student_id', $this->student->id)
            ->where('offense_id', $majorOffense->id)
            ->firstOrFail();
        $tribunalCase = TribunalCase::where('violation_record_id', $record->id)->firstOrFail();

        $this->assertSame($record->case_tracking_number, $tribunalCase->case_number);
        $this->assertSame($report->id, $tribunalCase->incident_report_id);
        $this->assertSame('Tribunal', $record->investigation_type);
    }

    public function test_assigning_a_case_to_sdt_adds_it_to_the_tribunal_pipeline(): void
    {
        $majorOffense = OffenseRule::factory()->create([
            'severity_level' => 'Major',
            'gravity' => 'minor',
            'requires_tribunal' => false,
        ]);
        $record = ViolationRecord::factory()->create([
            'student_id' => $this->student->id,
            'offense_id' => $majorOffense->id,
            'reported_by' => $this->staff->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('openAssignSDTModal', $record->id)
            ->set('selectedSDTMembers', ['member-1', 'member-2', 'member-3', 'member-4', 'member-5'])
            ->call('assignSDT');

        $this->assertDatabaseHas('violation_records', [
            'id' => $record->id,
            'assigned_to_sdt' => true,
            'investigation_type' => 'Tribunal',
        ]);
        $this->assertDatabaseHas('tribunal_cases', [
            'violation_record_id' => $record->id,
            'case_number' => $record->case_tracking_number,
        ]);
    }

    public function test_case_summary_tab_shows_records_in_period(): void
    {
        ViolationRecord::factory()->create([
            'student_id' => $this->student->id,
            'offense_id' => $this->offense->id,
            'reported_by' => $this->staff->id,
            'settled_by' => 'OSDW',
            'status' => 'Sanction Active',
            'created_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('setTab', 'case-summary')
            ->assertSee($this->student->name);
    }

    public function test_case_summary_shows_no_complaint_banner_for_empty_period(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('setTab', 'case-summary')
            ->set('reportYear', '2020')
            ->set('reportMonth', '01')
            ->assertSee('No complaint recorded within the period');
    }

    public function test_monthly_report_tab_renders(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('setTab', 'monthly-report')
            ->assertSee('January')
            ->assertSee('December');
    }

    public function test_by_type_report_tab_renders(): void
    {
        ViolationRecord::factory()->create([
            'student_id' => $this->student->id,
            'offense_id' => $this->offense->id,
            'reported_by' => $this->staff->id,
            'created_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('setTab', 'by-type-report')
            ->assertSee($this->offense->category);
    }

    public function test_export_case_summary_downloads_excel(): void
    {
        Excel::fake();

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('exportCaseSummary');

        $filename = 'case-summary-'.now()->format('Y-m-d').'.xlsx';
        Excel::assertDownloaded($filename);
    }

    public function test_export_monthly_report_downloads_excel(): void
    {
        Excel::fake();

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('exportMonthlyReport');

        $filename = 'monthly-report-'.now()->year.'.xlsx';
        Excel::assertDownloaded($filename);
    }

    public function test_export_by_type_report_downloads_excel(): void
    {
        Excel::fake();

        Livewire::actingAs($this->admin)
            ->test(CaseManagement::class)
            ->call('exportByTypeReport');

        $filename = 'by-type-report-'.now()->format('Y-m-d').'.xlsx';
        Excel::assertDownloaded($filename);
    }

    public function test_case_summary_export_contains_correct_headings(): void
    {
        $export = new CaseSummaryExport(now()->startOfMonth(), now()->endOfMonth());
        $headings = $export->headings();

        $this->assertContains('Case #', $headings);
        $this->assertContains('Settled By', $headings);
        $this->assertContains('Action Taken', $headings);
        $this->assertContains('Sanction Imposed (Date & Time)', $headings);
        $this->assertContains('Sanction Effective (Date & Time)', $headings);
    }

    public function test_monthly_report_export_contains_correct_headings(): void
    {
        $export = new MonthlyReportExport((int) now()->year);
        $headings = $export->headings();

        $this->assertContains('Month', $headings);
        $this->assertContains('Settled by Dean', $headings);
        $this->assertContains('Settled by OSDW', $headings);
    }

    public function test_report_period_selector_monthly(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(CaseManagement::class);

        $component->set('reportPeriodType', 'monthly')
            ->set('reportMonth', '03')
            ->set('reportYear', '2025');

        $this->assertNotNull($component->get('reportMonth'));
        $this->assertEquals('03', $component->get('reportMonth'));
    }

    public function test_report_period_selector_quarterly(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(CaseManagement::class);

        $component->set('reportPeriodType', 'quarterly')
            ->set('reportQuarter', '2')
            ->set('reportYear', '2025');

        $this->assertEquals('2', $component->get('reportQuarter'));
    }

    public function test_report_period_selector_custom(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(CaseManagement::class);

        $component->set('reportPeriodType', 'custom')
            ->set('reportStartDate', '2025-01-01')
            ->set('reportEndDate', '2025-06-30');

        $this->assertEquals('2025-01-01', $component->get('reportStartDate'));
        $this->assertEquals('2025-06-30', $component->get('reportEndDate'));
    }
}
