<?php

namespace RentJeeves\ExternalApiBundle\Services\ClientsEnum;

use CreditJeeves\CoreBundle\Enum;

class SoapClientEnum extends Enum
{
    const YARDI_RESIDENT_TRANSACTIONS = 'soap.client.yardi.resident_transactions';

    const YARDI_RESIDENT_DATA = 'soap.client.yardi.resident_data';

    const YARDI_PAYMENT = 'soap.client.yardi.payment';

    const AMSI_CLIENT = 'soap.client.amsi';
}
