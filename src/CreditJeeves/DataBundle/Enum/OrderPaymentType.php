<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class OrderPaymentType extends Enum
{
    const BANK = 'bank';
    const CARD = 'card';
    const CASH = 'cash';
}
