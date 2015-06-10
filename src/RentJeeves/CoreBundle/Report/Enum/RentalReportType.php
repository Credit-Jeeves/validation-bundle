<?php

namespace RentJeeves\CoreBundle\Report\Enum;

use CreditJeeves\CoreBundle\Enum;

class RentalReportType extends Enum
{
    const POSITIVE = 'positive';
    const NEGATIVE = 'negative';
    const CLOSURE = 'closure';
}
