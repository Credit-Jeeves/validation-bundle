<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\DataBundle\Enum\Base;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class PaymentStatus extends Base
{
    const PENDING = 'pending';
    const COMPLETE = 'complete';
    const ERROR = 'error';
    const CANCELLED = 'cancelled';
}
