<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentFlaggedReason extends Enum
{
    const AMOUNT_LIMIT_EXCEEDED = 'amount_limit_exceeded';

    const EXECUTION_DISALLOWED = 'execution_disallowed';

    const DTR_FIRST_PAYMENT = 'dtr_first_payment';

    const OUTSIDE_DTR_ROLLING_WINDOW = 'outside_dtr_rolling_window';

    const DTR_MONTH_LIMIT_OVERFLOWED = 'dtr_month_limit_overflowed';

    const DTR_UNTRUSTED_LANDLORD = 'dtr_untrusted_landlord';

    const DTR_PAYMENT_TO_UNTRUSTED_LANDLORD = 'dtr_payment_to_untrusted_landlord';

    const DTR_PAYMENT_MATCH_ADDRESSES = 'dtr_payment_match_addresses';
}
