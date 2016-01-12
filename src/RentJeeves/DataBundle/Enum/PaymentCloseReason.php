<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentCloseReason extends Enum
{
    const USER_CANCELLED = 'user_cancelled';

    const EXECUTED = 'executed';

    const DELETED = 'payment_account_deleted';

    const CONTRACT_CHANGED = 'contract_changed';

    const CONTRACT_FINISHED = 'contract_finished';

    const CONTRACT_DELETED = 'contract_deleted';

    /**
     * Used when recurring payment with CreditCard payment source failed.
     */
    const RECURRING_ERROR = 'recurring_error';

    /**
     * Used when recurring payment with ACH payment source returned.
     */
    const RECURRING_RETURNED = 'recurring_returned';

    /**
     * Used when payment is closed due to migration to another payment processor
     */
    const PAYMENT_PROCESSOR_MIGRATION = 'payment_processor_migration';

    /**
     * Used when admin closes flagged payment.
     */
    const CLOSED_BY_ADMIN = 'closed_by_admin';
}
