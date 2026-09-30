<?php

namespace Database\Seeders;

use App\Models\OffenseRule;
use App\Models\User;
use App\Models\ViolationRecord;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StudentConductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive test data for the Student Conduct Management System.
     */
    public function run(): void
    {
        // Ensure roles exist
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        // Create test staff members (reporters)
        $staffMembers = User::factory()->staff()->count(3)->create();
        foreach ($staffMembers as $staff) {
            $staff->assignRole('staff');
        }

        // Create offense rules (institutional handbook)
        $offenseRules = [
            [
                'code' => 'AC-001',
                'title' => 'Academic Dishonesty',
                'description' => 'Engaging in any form of academic dishonesty including cheating, plagiarism, or falsification of academic work.',
                'category' => 'Academic',
                'severity_level' => 'Major',
                'standard_sanction' => 'Course Failure',
            ],
            [
                'code' => 'AC-002',
                'title' => 'Plagiarism',
                'description' => 'Submitting work that contains ideas, words, or data from another source without proper attribution.',
                'category' => 'Academic',
                'severity_level' => 'Major',
                'standard_sanction' => 'Grade Penalty',
            ],
            [
                'code' => 'BE-001',
                'title' => 'Disruptive Conduct',
                'description' => 'Behavior that interferes with the learning environment or institutional operations.',
                'category' => 'Behavioral',
                'severity_level' => 'Minor',
                'standard_sanction' => 'Written Warning',
            ],
            [
                'code' => 'BE-002',
                'title' => 'Verbal Harassment',
                'description' => 'Making offensive, derogatory, or threatening comments toward another member of the institutional community.',
                'category' => 'Behavioral',
                'severity_level' => 'Moderate',
                'standard_sanction' => 'Probation',
            ],
            [
                'code' => 'BE-003',
                'title' => 'Physical Altercation',
                'description' => 'Engaging in fighting or any physical violence on institutional property.',
                'category' => 'Behavioral',
                'severity_level' => 'Severe',
                'standard_sanction' => '10-Day Suspension',
            ],
            [
                'code' => 'PR-001',
                'title' => 'Failure to Comply with Policy',
                'description' => 'Refusal to follow established institutional policies and procedures.',
                'category' => 'Procedural',
                'severity_level' => 'Minor',
                'standard_sanction' => 'Written Warning',
            ],
            [
                'code' => 'SA-001',
                'title' => 'Safety Protocol Violation',
                'description' => 'Failure to adhere to established safety guidelines and protocols.',
                'category' => 'Safety',
                'severity_level' => 'Moderate',
                'standard_sanction' => 'Safety Training Requirement',
            ],
            [
                'code' => 'TE-001',
                'title' => 'Unauthorized System Access',
                'description' => 'Accessing institutional computer systems or networks without authorization.',
                'category' => 'Technology',
                'severity_level' => 'Major',
                'standard_sanction' => 'Account Suspension',
            ],
        ];

        foreach ($offenseRules as $rule) {
            OffenseRule::create($rule);
        }

        // Create test students with violation records
        $students = User::factory()->student()->count(5)->create();
        foreach ($students as $student) {
            $student->assignRole('student');

            // Create 2-4 random violations per student
            $violationCount = rand(2, 4);
            for ($i = 0; $i < $violationCount; $i++) {
                ViolationRecord::factory()
                    ->forStudent($student)
                    ->reportedBy($staffMembers->random())
                    ->create([
                        'offense_id' => OffenseRule::inRandomOrder()->first()->id,
                    ]);
            }
        }

        $this->command->info('Student Conduct test data seeded successfully!');
        $this->command->info('Created: '.OffenseRule::count().' offense rules');
        $this->command->info('Created: '.ViolationRecord::count().' violation records');
        $this->command->info('Created: '.$students->count().' test students');
        $this->command->info('Created: '.$staffMembers->count().' test staff members');
    }
}
