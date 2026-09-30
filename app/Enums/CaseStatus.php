<?php

namespace App\Enums;

enum CaseStatus: string
{
    case UNDER_INFORMAL_DISCUSSION = 'under_informal_discussion';
    case UNDER_FORMAL_INVESTIGATION = 'under_formal_investigation';
    case RESOLUTION_DRAFTED = 'resolution_drafted';
    case CASE_RESOLVED = 'case_resolved';

    public function validTransitions(): array
    {
        return match ($this) {
            self::UNDER_INFORMAL_DISCUSSION => [self::UNDER_FORMAL_INVESTIGATION],
            self::UNDER_FORMAL_INVESTIGATION => [self::RESOLUTION_DRAFTED],
            self::RESOLUTION_DRAFTED => [self::CASE_RESOLVED],
            self::CASE_RESOLVED => [],
        };
    }
}
