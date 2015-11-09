<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportSource extends Enum
{
    const CSV = 'csv';

    const INTEGRATED_API = 'integrated_api';
}
