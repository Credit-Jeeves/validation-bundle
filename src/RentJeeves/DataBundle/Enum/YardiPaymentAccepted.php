<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class YardiPaymentAccepted extends Enum
{
    const ANY = 0;

    const DO_NOT_ACCEPT = 1;

    const CASH_EQUIVALENT = 2;
}
