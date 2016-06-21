<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportLeaseResidentStatus extends Enum
{
    const CURRENT = 'current';

    const PAST = 'past';

    const FUTURE = 'future';
}
