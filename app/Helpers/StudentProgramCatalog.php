<?php

namespace App\Helpers;

final class StudentProgramCatalog
{
    /**
     * @var array<string, list<string>>
     */
    private const PROGRAMS_BY_COLLEGE = [
        'COLLEGE OF INFORMATION AND COMPUTING SCIENCES' => ['BSIT'],
        'COLLEGE OF BUSINESS ENTREPRENEURSHIP AND ACCOUNTANCY' => ['BSBA', 'BSA'],
        'COLLEGE OF HOSPITALITY MANAGEMENT' => ['BSHM'],
        'COLLEGE OF TEACHER EDUCATION' => ['BSED', 'BEED'],
        'COLLEGE OF FISHERIES AND AQUATIC SCIENCES' => ['BSFi'],
        'COLLEGE OF INDUSTRIAL TECHNOLOGY' => ['BSITech', 'BIT'],
        'COLLEGE OF CRIMINAL JUSTICE EDUCATION' => ['BSCrim'],
    ];

    /**
     * @return list<string>
     */
    public static function colleges(): array
    {
        return array_keys(self::PROGRAMS_BY_COLLEGE);
    }

    /**
     * @return list<string>
     */
    public static function programsForCollege(?string $college): array
    {
        return self::PROGRAMS_BY_COLLEGE[$college] ?? [];
    }

    public static function collegeForProgram(string $program): ?string
    {
        foreach (self::PROGRAMS_BY_COLLEGE as $college => $programs) {
            if (in_array($program, $programs, true)) {
                return $college;
            }
        }

        return null;
    }

    public static function belongsToCollege(?string $college, ?string $program): bool
    {
        return $college !== null
            && $program !== null
            && in_array($program, self::programsForCollege($college), true);
    }
}
