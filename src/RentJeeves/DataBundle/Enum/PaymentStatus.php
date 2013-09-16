<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * First status will be always active
 * Status close will be last status.
 * Avaliable chains:
 * 1. active
 * 2. active -> close
 * 3. active -> pause -> close
 * 4. active -> pause -> active -> close
 */
class PaymentStatus extends Enum
{
    /**
     * @var string
     */
    const ACTIVE = 'active';

    /**
     * @var string
     */
    const PAUSE = 'pause';

    /**
     * @var string
     */
    const CLOSE = 'close';
}
