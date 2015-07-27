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
    const REFUNDING = 'refunding';
    const REISSUED = 'reissued';
    const RETURNED = 'returned';
    const SENDING = 'sending';

    /**
     * @param STRING $current
     *
     * @return array
     */
    public static function getManualAvailableToSet($current)
    {
        $restrictedStatuses = array_diff(
            [OrderStatus::NEWONE, OrderStatus::COMPLETE, OrderStatus::ERROR, OrderStatus::SENDING],
            [$current]
        );

        return array_diff(
            OrderStatus::all(),
            $restrictedStatuses
        );
    }
}
