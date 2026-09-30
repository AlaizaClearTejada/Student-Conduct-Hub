<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register')
            ->assertSee('Student')
            ->assertSee('e.g., 24-00001')
            ->assertDontSee('OSDW Staff')
            ->assertDontSee('<option value="admin">');

        $this->assertStringContainsString('pattern="[0-9]{2}-[0-9]{5}"', $response->getContent());
    }

    public function test_new_users_can_register(): void
    {
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->set('student_id', '24-00001')
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('program', 'BSIT')
            ->set('year_level', '1st Year')
            ->set('section', 'A');

        $component->call('register');

        $component->assertHasNoErrors();
        $component->assertRedirect(route('student.dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->fresh()->hasRole('student'));
        $this->assertSame('student', auth()->user()->fresh()->role_type);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'student_id' => '24-00001',
            'role_type' => 'student',
            'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            'program' => 'BSIT',
            'year_level' => '1st Year',
            'section' => 'A',
        ]);
    }

    public function test_student_registration_rejects_invalid_student_id_format(): void
    {
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Invalid Student')
            ->set('email', 'invalid-student@example.com')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->set('student_id', '24-1234')
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('program', 'BSIT')
            ->set('year_level', '1st Year')
            ->set('section', 'A');

        $component->assertSee('Student ID must follow the format 00-00000');
        $component->call('register');

        $component->assertHasErrors(['student_id' => ['regex']]);
        $component->assertSee('The Student ID format must be strictly 00-00000 (e.g., 24-00001).');
        $this->assertDatabaseMissing('users', ['email' => 'invalid-student@example.com']);
    }

    public function test_student_registration_rejects_programs_from_another_college(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Invalid Student')
            ->set('email', 'invalid-program@example.com')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->set('student_id', '24-00002')
            ->set('college', 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES')
            ->set('program', 'BSBA')
            ->set('year_level', '1st Year')
            ->set('section', 'A');

        $component->call('register');

        $component->assertHasErrors(['program' => ['in']]);
        $this->assertDatabaseMissing('users', ['email' => 'invalid-program@example.com']);
    }
}
