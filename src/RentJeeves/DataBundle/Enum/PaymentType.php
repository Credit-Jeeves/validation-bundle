<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentType extends Enum
{
    /**
     * @var string
     */
    const RECURRING = 'recurring';

    /**
     * @var string
     */
    const ONE_TIME = 'one_time';

    /**
     * Don't use it!
     *
     * @var string
     */
    const IMMEDIATE = 'immediate';
}
