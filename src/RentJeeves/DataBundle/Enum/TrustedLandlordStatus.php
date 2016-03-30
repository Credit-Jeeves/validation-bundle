<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class TrustedLandlordStatus extends Enum
{
    const NEWONE = 'new';

    const TRUSTED = 'trusted';

    const RFI = 'rfi';

    const DENIED = 'denied';

    const IN_PROGRESS = 'in progress';
}
