<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportStatus extends Enum
{
    const RUNNING = 'running';

    const COMPLETE = 'complete';

    const ERROR = 'error';
}
