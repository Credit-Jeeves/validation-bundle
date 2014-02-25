<?php
namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * Order statuses
 * First status will be NEWONE for all orders
 * Avaliable chains:
 * 1. NEWONE -> (PENDING) -> COMPLETE
 * 2. NEWONE -> (PENDING) -> ERROR
 * 3. NEWONE -> (PENDING) -> CANCELLED
 * 4. NEWONE -> (PENDING) -> COMPLETE -> REFUNDED
 * 5. NEWONE -> (PENDING) -> COMPLETE -> RETURNED
 * @author Ton Sharp <66ton99@gmail.com>
 */
class OrderStatus extends Enum
{
    /**
     * @var string
     */
    const NEWONE = 'new';

    /**
     * @var string
     */
    const PENDING = 'pending';

    /**
     * @var string
     */
    const COMPLETE = 'complete';

    /**
     * @var string
     */
    const ERROR = 'error';

    /**
     * @var string
     */
    const CANCELLED = 'cancelled';

    /**
     * @var string
     */
    const REFUNDED = 'refunded';

    /**
     * @var string
     */
    const RETURNED = 'returned';

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
