<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 */
class PaymentAccountType extends Enum
{
    const PERSONAL_CHECKING = 'personal_checking';
    const BUSINESS_CHECKING = 'business_checking';
    const SAVINGS = 'savings';
    const CC = 'cc';
}
