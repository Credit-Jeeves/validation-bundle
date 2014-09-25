<?php

namespace RentJeeves\ExternalApiBundle\Soap;

use CreditJeeves\CoreBundle\Enum;

class SoapClientEnum extends Enum
{
    const RESIDENT = 'soap.client.yardi.resident';

    const PAYMENT = 'soap.client.yardi.payment';
}
