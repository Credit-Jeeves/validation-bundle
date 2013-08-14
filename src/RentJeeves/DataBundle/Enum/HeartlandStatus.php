<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class HeartlandStatus extends \CreditJeeves\CoreBundle\Enum
{
    const PENDING = 'pending';
    const REFUNDED = 'refunded';
    const FINISHED = 'finished';
    const CANCELLED = 'cancelled';
}
