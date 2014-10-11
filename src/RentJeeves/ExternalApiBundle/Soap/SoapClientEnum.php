<?php

namespace RentJeeves\ExternalApiBundle\Soap;

use CreditJeeves\CoreBundle\Enum;

class SoapClientEnum extends Enum
{
    const RESIDENT_TRANSACTIONS = 'soap.client.yardi.resident_transactions';

    const RESIDENT_DATA = 'soap.client.yardi.resident_data';

    const PAYMENT = 'soap.client.yardi.payment';
}
