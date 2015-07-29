<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class DepositAccountType extends Enum
{
    const APPLICATION_FEE = 'application_fee';
    const SECURITY_DEPOSIT = 'security_deposit';
    const RENT = 'rent';
}
