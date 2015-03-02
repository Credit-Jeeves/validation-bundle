<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ApiIntegrationType extends Enum
{
    const NONE = 'none';

    const YARDI_VOYAGER = 'yardi voyager';

    const RESMAN = 'resman';

    const MRI = 'mri';

    public static $importMapping = [
        self::YARDI_VOYAGER => 'yardi',
        self::RESMAN        => 'resman'
    ];
}
