<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentAccountType extends Enum
{
    const BANK = 'bank';
    const CREDIT_CARD = 'card';
    const DEBIT_CARD = 'debit_card';
}
