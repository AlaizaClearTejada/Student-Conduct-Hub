<?php

namespace Tests\Feature;

use App\Imports\StudentImport;
use App\Livewire\Staff\StudentRoster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'administrator']);

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
    }

    public function test_student_roster_shows_import_button(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->assertSee('Import Excel File');
    }

    public function test_import_modal_opens_and_closes(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->call('openImportModal')
            ->assertSet('showImportModal', true)
            ->call('closeImportModal')
            ->assertSet('showImportModal', false);
    }

    public function test_downloadable_template_uses_required_id_and_catalog_pair(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.students.import-template'));

        $response->assertOk();
        $rows = array_map('str_getcsv', explode("\n", trim($response->getContent())));

        $this->assertSame(['Student ID', 'Name', 'Program', 'College', 'Year Level', 'Section', 'Email'], $rows[0]);
        $this->assertSame([
            '24-00001',
            'Dela Cruz, Juan',
            'BSIT',
            'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
            '1st Year',
            'A',
            'jdelacruz@csu.edu.ph',
        ], $rows[1]);

        $import = new StudentImport;
        $import->collection(collect($rows));

        $this->assertSame(1, $import->importedCount);
        $this->assertSame(0, $import->skippedCount);
    }

    public function test_import_rejects_unsupported_file_types(): void
    {
        Livewire::actingAs($this->staff)
            ->test(StudentRoster::class)
            ->call('openImportModal')
            ->set('importFile', UploadedFile::fake()->create('students.txt', 1, 'text/plain'))
            ->assertHasErrors(['importFile' => ['mimes']]);
    }

    public function test_student_import_creates_users_from_collection(): void
    {
        $import = new StudentImport;

        $rows = $this->importRows([
            [
                'student_id' => '25-00001',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan.delacruz@csu.edu.ph',
                'program' => 'BSIT',
                'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
                'year_level' => '2nd Year',
                'section' => 'A',
            ],
            [
                'student_id' => '25-00002',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'maria.santos@csu.edu.ph',
                'program' => 'BSIT',
                'college' => 'COLLEGE OF INFORMATION AND COMPUTING SCIENCES',
                'year_level' => '1st Year',
                'section' => 'B',
            ],
        ]);

        $import->collection($rows);

        $this->assertEquals(2, $import->importedCount);
        $this->assertEquals(0, $import->skippedCount);

        $this->assertSame('25-00001', $import->validRows[0]['student_id']);
        $this->assertSame('Juan', $import->validRows[0]['first_name']);
        $this->assertSame('Dela Cruz', $import->validRows[0]['last_name']);
        $this->assertSame('student', $import->validRows[0]['role_type']);
        $this->assertSame('25-00002', $import->validRows[1]['student_id']);
    }

    public function test_student_import_skips_duplicate_student_id(): void
    {
        User::factory()->student()->create(['student_id' => '25-00001']);

        $import = new StudentImport;

        $rows = $this->importRows([
            [
                'student_id' => '25-00001',
                'first_name' => 'Duplicate',
                'last_name' => 'Student',
                'email' => 'dup@csu.edu.ph',
                'program' => 'BSIT',
                'college' => 'Test College',
                'year_level' => '1st Year',
                'section' => 'A',
            ],
        ]);

        $import->collection($rows);

        $this->assertEquals(0, $import->importedCount);
        $this->assertEquals(1, $import->skippedCount);
        $this->assertCount(1, $import->errors);
        $this->assertEquals('Student ID already exists', $import->errors[0]['reason']);
    }

    public function test_student_import_auto_generates_email_when_empty(): void
    {
        $import = new StudentImport;

        $rows = $this->importRows([
            [
                'student_id' => '25-00099',
                'first_name' => 'Pedro',
                'last_name' => 'Garcia',
                'email' => '',
                'program' => 'BSBA',
                'college' => 'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY',
                'year_level' => '3rd Year',
                'section' => 'C',
            ],
        ]);

        $import->collection($rows);

        $this->assertEquals(1, $import->importedCount);

        $generatedEmail = $import->validRows[0]['email'];

        $this->assertStringContainsString('pedro', strtolower($generatedEmail));
        $this->assertStringContainsString('@csu.edu.ph', $generatedEmail);
    }

    public function test_student_import_skips_empty_student_id_rows(): void
    {
        $import = new StudentImport;

        $rows = $this->importRows([
            [
                'student_id' => '',
                'first_name' => 'Empty',
                'last_name' => 'Row',
                'email' => 'empty@test.com',
                'program' => '',
                'college' => '',
                'year_level' => '',
                'section' => '',
            ],
        ]);

        $import->collection($rows);

        $this->assertEquals(0, $import->importedCount);
        $this->assertEquals(0, $import->skippedCount);
        $this->assertCount(0, $import->errors);
    }

    /**
     * @param  list<array<string, string>>  $rows
     */
    private function importRows(array $rows): Collection
    {
        $headers = ['Student ID', 'Name', 'Program', 'College', 'Year Level', 'Section', 'Email'];
        $dataRows = array_map(fn (array $row) => [
            $row['student_id'] ?? '',
            ($row['last_name'] ?? '').', '.($row['first_name'] ?? ''),
            $row['program'] ?? '',
            $row['college'] ?? '',
            $row['year_level'] ?? '',
            $row['section'] ?? '',
            $row['email'] ?? '',
        ], $rows);

        return collect([$headers, ...$dataRows]);
    }
}
