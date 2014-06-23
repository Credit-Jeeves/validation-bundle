<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 */
class PaymentAccountType extends Enum
{
    /**
     * ACH
     * @var string
     */
    const BANK = 'bank';

    /**
     * CC
     * @var string
     */
    const CARD = 'card';
}
