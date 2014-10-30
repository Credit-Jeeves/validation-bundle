<?php

namespace RentJeeves\ApiBundle\Forms\Enum;

use CreditJeeves\CoreBundle\Enum;

class ReportingType extends Enum
{
    const ENABLED = 'enabled';

    const DISABLED = 'disabled';

    public static function getMapValue($reportingType)
    {
        if ($reportingType === self::ENABLED) {
            return true;
        }

        return false;
    }
}
