<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class TrustedLandlordStatus extends Enum
{
    const INITIATED = 'initiated';

    const FAILED = 'failed';

    const NEWONE = 'new';

    const TRUSTED = 'trusted';

    const DENIED = 'denied';

    const IN_PROGRESS = 'in progress';

    const WAITING_FOR_INFO = 'waiting for info';
}
