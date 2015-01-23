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

    /**
     * Used when recurring payment with CreditCard payment source failed.
     *
     * @var string
     */
    const RECURRING_ERROR = 'recurring_error';

    /**
     * Used when recurring payment with ACH payment source returned.
     *
     * @var string
     */
    const RECURRING_RETURNED = 'recurring_returned';
}
