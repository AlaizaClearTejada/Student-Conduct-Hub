<?php

namespace App\Imports;

use App\Helpers\StudentProgramCatalog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection
{
    public int $importedCount = 0;

    public int $skippedCount = 0;

    public array $validRows = [];

    public array $errors = [];

    /**
     * Return the downloadable sample CSV with catalog-valid student data.
     */
    public static function templateCsv(): string
    {
        $college = StudentProgramCatalog::colleges()[0];
        $program = StudentProgramCatalog::programsForCollege($college)[0];
        $rows = [
            ['Student ID', 'Name', 'Program', 'College', 'Year Level', 'Section', 'Email'],
            ['24-00001', 'Dela Cruz, Juan', $program, $college, '1st Year', 'A', 'jdelacruz@csu.edu.ph'],
        ];
        $stream = fopen('php://temp', 'r+');

        foreach ($rows as $row) {
            fputcsv($stream, $row, escape: '', eol: "\n");
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv === false ? '' : $csv;
    }

    public function collection(Collection $rows): void
    {
        $headerRowIndex = -1;
        $columnMap = [
            'student_id' => -1,
            'name' => -1,
            'first_name' => -1,
            'last_name' => -1,
            'program' => -1,
            'college' => -1,
            'year_level' => -1,
            'section' => -1,
            'email' => -1,
        ];

        foreach ($rows as $index => $row) {
            $rowArray = is_array($row) ? $row : $row->toArray();

            // Auto-detect header row
            if ($headerRowIndex === -1) {
                $foundId = -1;
                $foundName = -1;

                foreach ($rowArray as $colIndex => $cellValue) {
                    $val = strtolower(trim((string) $cellValue));
                    // Look for common ID headers leniently
                    if (str_contains($val, 'id') || str_contains($val, 'code')) {
                        $foundId = $colIndex;
                    }
                    if (str_contains($val, 'name')) {
                        $foundName = $colIndex;
                    }
                }

                // If we found both an ID column and a Name column, this is likely our header row!
                if ($foundId !== -1 && $foundName !== -1) {
                    $headerRowIndex = $index;
                    $columnMap['student_id'] = $foundId;
                    $columnMap['name'] = $foundName;

                    // Map the rest of the columns
                    foreach ($rowArray as $colIndex => $cellValue) {
                        $val = strtolower(trim((string) $cellValue));
                        if (str_contains($val, 'program') || str_contains($val, 'course')) {
                            $columnMap['program'] = $colIndex;
                        } elseif (str_contains($val, 'college')) {
                            $columnMap['college'] = $colIndex;
                        } elseif (str_contains($val, 'year')) {
                            $columnMap['year_level'] = $colIndex;
                        } elseif (str_contains($val, 'section')) {
                            $columnMap['section'] = $colIndex;
                        } elseif (str_contains($val, 'email')) {
                            $columnMap['email'] = $colIndex;
                        } elseif (str_contains($val, 'first')) {
                            $columnMap['first_name'] = $colIndex;
                        } elseif (str_contains($val, 'last')) {
                            $columnMap['last_name'] = $colIndex;
                        }
                    }
                }

                // Skip the row regardless (either it's junk above the header, or it IS the header)
                continue;
            }

            // Now we are parsing actual data rows!
            $studentId = $columnMap['student_id'] !== -1 ? trim((string) ($rowArray[$columnMap['student_id']] ?? '')) : '';

            // If it's an empty row in the middle or end of the file, just skip without error
            if (empty($studentId)) {
                continue;
            }

            // Validate Student ID format (XX-XXXXX)
            if (! preg_match('/^\d{2}-\d{5}$/', $studentId)) {
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $index + 1,
                    'student_id' => $studentId,
                    'reason' => 'Invalid ID format (Expected format: 00-00000)',
                ];

                continue;
            }

            // Skip duplicate student IDs (including archived)
            if (User::withTrashed()->where('student_id', $studentId)->exists()) {
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $index + 1,
                    'student_id' => $studentId,
                    'reason' => 'Student ID already exists',
                ];

                continue;
            }

            // Parse names
            $firstName = $columnMap['first_name'] !== -1 ? trim((string) ($rowArray[$columnMap['first_name']] ?? '')) : '';
            $lastName = $columnMap['last_name'] !== -1 ? trim((string) ($rowArray[$columnMap['last_name']] ?? '')) : '';
            $rawName = $columnMap['name'] !== -1 ? trim((string) ($rowArray[$columnMap['name']] ?? '')) : '';

            if (empty($firstName) && empty($lastName) && ! empty($rawName)) {
                if (str_contains($rawName, ',')) {
                    $parts = explode(',', $rawName, 2);
                    $lastName = trim($parts[0]);
                    $firstName = trim($parts[1] ?? '');
                } else {
                    $parts = explode(' ', $rawName);
                    if (count($parts) > 1) {
                        $lastName = array_pop($parts);
                        $firstName = implode(' ', $parts);
                    } else {
                        $firstName = $rawName;
                    }
                }
            }

            $email = $columnMap['email'] !== -1 ? trim((string) ($rowArray[$columnMap['email']] ?? '')) : '';

            // Auto-generate email if not provided
            if (empty($email)) {
                $email = Str::lower($firstName.'.'.$lastName.'.'.$studentId).'@csu.edu.ph';
                $email = preg_replace('/[^a-z0-9.@]/', '', $email);
            }

            // Skip if email already exists (including archived)
            if (User::withTrashed()->where('email', $email)->exists()) {
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $index + 1,
                    'student_id' => $studentId,
                    'reason' => 'Email already exists',
                ];

                continue;
            }

            // Parse year level
            $yearLevel = $columnMap['year_level'] !== -1 ? trim((string) ($rowArray[$columnMap['year_level']] ?? '')) : '';
            if (in_array($yearLevel, ['1', '2', '3', '4'])) {
                $suffix = match ($yearLevel) {
                    '1' => 'st',
                    '2' => 'nd',
                    '3' => 'rd',
                    default => 'th'
                };
                $yearLevel = $yearLevel.$suffix.' Year';
            }

            $program = $columnMap['program'] !== -1 ? trim((string) ($rowArray[$columnMap['program']] ?? '')) : 'N/A';
            $college = $columnMap['college'] !== -1 ? trim((string) ($rowArray[$columnMap['college']] ?? '')) : 'N/A';
            $section = $columnMap['section'] !== -1 ? trim((string) ($rowArray[$columnMap['section']] ?? '')) : 'N/A';

            if (! StudentProgramCatalog::belongsToCollege($college, $program)) {
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $index + 1,
                    'student_id' => $studentId,
                    'reason' => 'Program does not belong to the selected college',
                ];

                continue;
            }

            $this->validRows[] = [
                'name' => trim($firstName.' '.$lastName),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                // We'll generate and hash the password during confirmImport to save time during preview
                'student_id' => $studentId,
                'program' => $program,
                'college' => $college,
                'role_type' => 'student',
                'year_level' => $yearLevel,
                'section' => $section,
                'email_verified_at' => now(),
            ];

            $this->importedCount++;
        }

        // If we processed all rows and never found a header, report it
        if ($headerRowIndex === -1 && count($rows) > 0) {
            $this->errors[] = [
                'row' => 1,
                'student_id' => 'Missing Header',
                'reason' => 'Could not detect a header row. Ensure your file has a row containing "Code/ID" and "Name".',
            ];
        }
    }
}
