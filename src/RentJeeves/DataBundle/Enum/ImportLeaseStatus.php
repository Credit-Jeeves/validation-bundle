<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportLeaseStatus extends Enum
{
    const NEWONE = 'new';

    const MATCH = 'match';

    const ERROR = 'error';
}
