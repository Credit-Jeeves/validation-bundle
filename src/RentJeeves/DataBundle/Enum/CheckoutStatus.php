<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class CheckoutStatus extends \CreditJeeves\CoreBundle\Enum
{
    const PENDING = 'pending';
    const RETURNED = 'returned';
    const PRCESSED = 'processed';
    const CANCELLED = 'cancelled';
}
