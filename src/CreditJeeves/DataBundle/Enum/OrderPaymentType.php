<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class OrderPaymentType extends Enum
{
    const BANK = 'bank';
    const CARD = 'card';
    const CASH = 'cash';
    const SCANNED_CHECK = 'scanned_check';
}
