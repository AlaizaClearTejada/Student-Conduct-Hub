<?php

namespace Tests\Feature;

use App\Helpers\StudentProgramCatalog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\StudentConductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_accounts_are_seeded_with_valid_roles_and_academic_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $students = [
            'student@example.com' => [
                'student_id' => '24-00001',
                'program' => 'BSIT',
                'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            ],
            'student2@example.com' => [
                'student_id' => '24-00002',
                'program' => 'BSBA',
                'college' => 'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY',
            ],
            'student3@example.com' => [
                'student_id' => '24-00003',
                'program' => 'BSHM',
                'college' => 'COLLEGE OF HOSPITALITY MANAGEMENT',
            ],
            'tempstudent@example.com' => [
                'student_id' => '24-00004',
                'program' => 'BSED',
                'college' => 'COLLEGE OF TEACHER EDUCATION',
            ],
        ];

        foreach ($students as $email => $academicData) {
            $student = User::where('email', $email)->firstOrFail();

            $this->assertSame('student', $student->role_type);
            $this->assertSame($academicData['student_id'], $student->student_id);
            $this->assertMatchesRegularExpression('/^\d{2}-\d{5}$/', $student->student_id);
            $this->assertSame($academicData['program'], $student->program);
            $this->assertSame($academicData['college'], $student->college);
            $this->assertTrue(StudentProgramCatalog::belongsToCollege($student->college, $student->program));
            $this->assertTrue($student->hasRole('student'));
        }

        $staff = User::where('email', 'staff@example.com')->firstOrFail();

        $this->assertSame('osdw_staff', $staff->role_type);
        $this->assertTrue($staff->hasRole('staff'));
        $this->assertNull($staff->program);
        $this->assertNull($staff->college);
        $this->assertNull($staff->year_level);
        $this->assertNull($staff->section);

        $administrators = User::where('role_type', 'admin')->get();

        $this->assertCount(2, $administrators);
        $this->assertEqualsCanonicalizing(
            ['rnishimura062@gmail.com', 'deseoharvey5@gmail.com'],
            $administrators->pluck('email')->all(),
        );

        foreach ($administrators as $administrator) {
            $this->assertTrue($administrator->hasRole('administrator'));
            $this->assertNull($administrator->program);
            $this->assertNull($administrator->college);
            $this->assertNull($administrator->year_level);
            $this->assertNull($administrator->section);
        }
    }

    public function test_student_conduct_seeder_uses_student_and_staff_role_types(): void
    {
        $this->seed([RoleSeeder::class, StudentConductSeeder::class]);

        $students = User::where('role_type', 'student')->get();
        $staffMembers = User::where('role_type', 'osdw_staff')->get();

        $this->assertCount(5, $students);
        $this->assertCount(3, $staffMembers);

        foreach ($students as $student) {
            $this->assertMatchesRegularExpression('/^\d{2}-\d{5}$/', $student->student_id);
            $this->assertTrue(StudentProgramCatalog::belongsToCollege($student->college, $student->program));
            $this->assertTrue($student->hasRole('student'));
        }

        foreach ($staffMembers as $staffMember) {
            $this->assertTrue($staffMember->hasRole('staff'));
            $this->assertNull($staffMember->program);
            $this->assertNull($staffMember->college);
            $this->assertNull($staffMember->year_level);
            $this->assertNull($staffMember->section);
        }
    }
}
