<?php

namespace Tests\Feature;

use App\Livewire\Staff\StudentForm;
use App\Livewire\Staff\StudentRoster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->student = User::factory()->student()->create();
        $this->student->assignRole('student');
    }

    // ─── Route Authorization ────────────────────────────────────────────────

    public function test_staff_can_access_student_roster_page(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.students'));

        $response->assertStatus(200);
    }

    public function test_student_cannot_access_student_roster_page(): void
    {
        $response = $this->actingAs($this->student)->get(route('staff.students'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_from_student_roster_page(): void
    {
        $response = $this->get(route('staff.students'));

        $response->assertRedirect(route('login'));
    }

    // ─── StudentRoster Component ────────────────────────────────────────────

    public function test_roster_lists_student_role_users(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->assertSee($this->student->student_id);
    }

    public function test_roster_search_filters_by_student_id(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->set('search', $this->student->student_id)
            ->assertSee($this->student->student_id);
    }

    public function test_roster_search_shows_empty_state_for_unknown_id(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->set('search', 'NONEXISTENT-0000')
            ->assertSee('No students found matching');
    }

    public function test_roster_view_action_redirects_to_student_profile(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->call('viewStudent', $this->student->id)
            ->assertRedirect(route('staff.students.show', $this->student->id));
    }

    public function test_roster_create_action_redirects_to_create_page(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->call('openCreateForm')
            ->assertRedirect(route('staff.students.create'));
    }

    // ─── StudentForm: Create ────────────────────────────────────────────────

    public function test_form_mounts_in_create_mode(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class, ['mode' => 'create'])
            ->assertSet('mode', 'create')
            ->assertSet('isEditing', false);
    }

    public function test_form_mounts_in_edit_mode_and_prepopulates_fields(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class, ['mode' => 'edit', 'studentId' => $this->student->id])
            ->assertSet('mode', 'edit')
            ->assertSet('isEditing', true)
            ->assertSet('studentIdInput', $this->student->student_id)
            ->assertSet('email', $this->student->email);
    }

    public function test_student_id_taken_flag_sets_when_id_already_exists(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class)
            ->set('studentIdInput', $this->student->student_id)
            ->assertSet('studentIdTaken', true);
    }

    public function test_student_id_taken_flag_clears_for_available_id(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class)
            ->set('studentIdInput', '99-99999')
            ->assertSet('studentIdTaken', false);
    }

    public function test_creating_valid_student_persists_record(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class, ['mode' => 'create'])
            ->set('studentIdInput', '25-00001')
            ->set('firstName', 'Maria')
            ->set('lastName', 'Santos')
            ->set('email', 'maria.santos@csu.edu.ph')
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('program', 'BSIT')
            ->set('yearLevel', '1st Year')
            ->set('section', 'A')
            ->call('save')
            ->assertRedirect(route('staff.students'));

        $this->assertDatabaseHas('users', [
            'student_id' => '25-00001',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'name' => 'Maria Santos',
            'email' => 'maria.santos@csu.edu.ph',
            'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            'year_level' => '1st Year',
            'section' => 'A',
        ]);

        $newUser = User::where('student_id', '25-00001')->first();
        $this->assertTrue($newUser->hasRole('student'));
    }

    public function test_creating_student_with_duplicate_id_fails_validation(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class, ['mode' => 'create'])
            ->set('studentIdInput', $this->student->student_id)
            ->set('firstName', 'Jose')
            ->set('lastName', 'Reyes')
            ->set('email', 'jose.reyes@csu.edu.ph')
            ->set('college', 'COLLEGE OF TEACHER EDUCATION')
            ->set('yearLevel', '2nd Year')
            ->set('section', 'B')
            ->call('save')
            ->assertHasErrors(['studentIdInput']);
    }

    public function test_creating_student_with_invalid_college_fails_validation(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class, ['mode' => 'create'])
            ->set('studentIdInput', '25-00099')
            ->set('firstName', 'Ana')
            ->set('lastName', 'Lopez')
            ->set('email', 'ana.lopez@csu.edu.ph')
            ->set('college', 'COLLEGE OF FAKE SCIENCES')
            ->set('yearLevel', '1st Year')
            ->set('section', 'A')
            ->call('save')
            ->assertHasErrors(['college']);
    }

    public function test_missing_required_fields_fail_validation(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class, ['mode' => 'create'])
            ->call('save')
            ->assertHasErrors(['studentIdInput', 'firstName', 'lastName', 'email', 'college', 'yearLevel', 'section']);
    }

    // ─── StudentForm: Edit ──────────────────────────────────────────────────

    public function test_editing_student_updates_existing_record(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class, ['mode' => 'edit', 'studentId' => $this->student->id])
            ->set('firstName', 'Updated')
            ->set('lastName', 'Name')
            ->set('yearLevel', '3rd Year')
            ->call('save')
            ->assertRedirect(route('staff.students'));

        $this->student->refresh();
        $this->assertEquals('Updated', $this->student->first_name);
        $this->assertEquals('Name', $this->student->last_name);
        $this->assertEquals('Updated Name', $this->student->name);
        $this->assertEquals('3rd Year', $this->student->year_level);
    }
}
