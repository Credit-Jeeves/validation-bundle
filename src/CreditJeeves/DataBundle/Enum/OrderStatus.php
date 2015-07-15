<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * Order statuses
 * First status will be NEWONE for all orders
*/
class OrderStatus extends Enum
{
    const CANCELLED = 'cancelled';
    const COMPLETE = 'complete';
    const ERROR = 'error';
    const NEWONE = 'new';
    const PENDING = 'pending';
    const REFUNDED = 'refunded';
    const RETURNED = 'returned';
    const SENDING = 'sending';

    public static function getManualAvailableToSet($current)
    {
        $restrictedStatuses = array_diff(
            array(OrderStatus::NEWONE, OrderStatus::COMPLETE, OrderStatus::ERROR),
            array($current)
        );

        return array_diff(
            OrderStatus::all(),
            $restrictedStatuses
        );
    }
}
