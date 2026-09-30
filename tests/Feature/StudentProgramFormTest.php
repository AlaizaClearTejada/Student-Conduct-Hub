<?php

namespace Tests\Feature;

use App\Livewire\Staff\StudentForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentProgramFormTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
    }

    public function test_program_options_follow_the_selected_college(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class)
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->assertSet('programs', ['BSIT'])
            ->assertSee('BSIT')
            ->assertDontSee('BSBA');
    }

    public function test_all_csu_aparri_colleges_load_only_their_programs(): void
    {
        $component = Livewire::actingAs($this->staff)->test(StudentForm::class);

        $programsByCollege = [
            'COLLEGE OF INFORMATION AND COMPUTING SCIENCES' => ['BSIT'],
            'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY' => ['BSBA', 'BSA'],
            'COLLEGE OF HOSPITALITY MANAGEMENT' => ['BSHM'],
            'COLLEGE OF TEACHER EDUCATION' => ['BSED', 'BEED'],
            'COLLEGE OF FISHERIES AND AQUATIC SCIENCES' => ['BSFi'],
            'COLLEGE OF INDUSTRIAL TECHNOLOGY' => ['BSITech', 'BIT'],
            'COLLEGE OF CRIMINAL JUSTICE EDUCATION' => ['BSCrim'],
        ];

        $component->assertSet('colleges', array_keys($programsByCollege));

        foreach ($programsByCollege as $college => $programs) {
            $component->set('college', $college)->assertSet('programs', $programs);
        }
    }

    public function test_student_cannot_be_saved_with_a_program_from_another_college(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentForm::class)
            ->set('studentIdInput', '25-00001')
            ->set('firstName', 'Maria')
            ->set('lastName', 'Santos')
            ->set('email', 'maria.santos@csu.edu.ph')
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('program', 'BSBA')
            ->set('yearLevel', '1st Year')
            ->set('section', 'A')
            ->call('save')
            ->assertHasErrors(['program' => ['in']]);

        $this->assertDatabaseMissing('users', ['email' => 'maria.santos@csu.edu.ph']);
    }

    public function test_created_student_has_student_role_type(): void
    {
        Mail::fake();

        Livewire::actingAs($this->staff)
            ->test(StudentForm::class)
            ->set('studentIdInput', '25-00002')
            ->set('firstName', 'Jose')
            ->set('lastName', 'Reyes')
            ->set('email', 'jose.reyes@csu.edu.ph')
            ->set('college', 'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY')
            ->set('program', 'BSBA')
            ->set('yearLevel', '2nd Year')
            ->set('section', 'B')
            ->call('save')
            ->assertRedirect(route('staff.students'));

        $student = User::where('email', 'jose.reyes@csu.edu.ph')->firstOrFail();

        $this->assertSame('student', $student->role_type);
        $this->assertTrue($student->hasRole('student'));
    }
}
