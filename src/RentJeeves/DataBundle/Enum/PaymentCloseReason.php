<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentCloseReason extends Enum
{
    /**
     * @var string
     */
    const USER_CANCELLED = 'user_cancelled';
    /**
     * @var string
     */
    const EXECUTED = 'executed';
    /**
     * @var string
     */
    const DELETED = 'payment_account_deleted';
    /**
     * @var string
     */
    const CONTRACT_CHANGED = 'contract_changed';
    /**
     * @var string
     */
    const CONTRACT_DELETED = 'contract_deleted';
}
