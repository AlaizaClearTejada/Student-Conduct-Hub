<?php

namespace App\Enums;

enum CaseStatus: string
{
    case UNDER_INVESTIGATION = 'under_investigation';
    case NOTICE_ISSUED = 'notice_issued';
    case HEARING_SCHEDULED = 'hearing_scheduled';
    case RESOLUTION_DRAFTED = 'resolution_drafted';
    case CLOSED = 'closed';
    case APPEALED = 'appealed';

    /**
     * @return list<self>
     */
    public function validTransitions(): array
    {
        return match ($this) {
            self::UNDER_INVESTIGATION => [self::NOTICE_ISSUED],
            self::NOTICE_ISSUED => [self::HEARING_SCHEDULED],
            self::HEARING_SCHEDULED => [self::RESOLUTION_DRAFTED],
            self::RESOLUTION_DRAFTED => [self::CLOSED, self::APPEALED],
            self::CLOSED, self::APPEALED => [],
        };
    }
}
