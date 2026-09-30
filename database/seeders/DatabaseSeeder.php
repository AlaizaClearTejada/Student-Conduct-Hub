<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RoleSeeder::class,
            CSUOffenseRuleSeeder::class,
            DecisionSupportSeeder::class,
        ]);

        // Create Administrator account
        $admin = User::factory()->administrator()->create([
            'name' => 'Admin User',
            'email' => 'rnishimura062@gmail.com',
            'password' => 'password',
        ]);
        $admin->assignRole('administrator');

        // Create Custom Admin Account
        $customAdmin = User::factory()->administrator()->create([
            'name' => 'SCMS Admin',
            'email' => 'deseoharvey5@gmail.com',
            'password' => 'password', // Default password for testing
        ]);
        $customAdmin->assignRole('administrator');

        // Create Staff account
        $staff = User::factory()->staff()->create([
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
            'password' => 'password',
        ]);
        $staff->assignRole('staff');

        // Create Student accounts
        $student1 = User::factory()->student()->create([
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'student@example.com',
            'password' => 'password',
            'student_id' => '24-00001',
            'program' => 'BSIT',
            'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
        ]);
        $student1->assignRole('student');

        $student2 = User::factory()->student()->create([
            'name' => 'Jane Smith',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'student2@example.com',
            'password' => 'password',
            'student_id' => '24-00002',
            'program' => 'BSBA',
            'college' => 'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY',
        ]);
        $student2->assignRole('student');

        $student3 = User::factory()->student()->create([
            'name' => 'Mike Johnson',
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'email' => 'student3@example.com',
            'password' => 'password',
            'student_id' => '24-00003',
            'program' => 'BSHM',
            'college' => 'COLLEGE OF HOSPITALITY MANAGEMENT',
        ]);
        $student3->assignRole('student');

        // Temporary Student account
        $student4 = User::factory()->student()->create([
            'name' => 'Temp Student',
            'first_name' => 'Temp',
            'last_name' => 'Student',
            'email' => 'tempstudent@example.com',
            'password' => 'password',
            'student_id' => '24-00004',
            'program' => 'BSED',
            'college' => 'COLLEGE OF TEACHER EDUCATION',
        ]);
        $student4->assignRole('student');

        $this->command->info('Test accounts created successfully!');
        $this->command->info('Administrators: rnishimura062@gmail.com, deseoharvey5@gmail.com / password');
        $this->command->info('Staff: staff@example.com / password');
        $this->command->info('Students: student@example.com, student2@example.com, student3@example.com, tempstudent@example.com / password');
    }
}
