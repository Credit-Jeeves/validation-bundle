<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class DepositAccountStatus extends Enum
{
    /**
     * Used when HPS redirects to the error endpoint.
     */
    const HPS_ERROR = 'error';

    /**
     * Used when HPS redirects to the success endpoint.
     */
    const HPS_SUCCESS = 'success';

    /**
     * First status of deposit account.
     * Set when landlord is redirected to HPS to get MerchantName.
     */
    const DA_INIT = 'init';

    /**
     * Set when merchant name exists.
     */
    const DA_COMPLETE = 'complete';
}
