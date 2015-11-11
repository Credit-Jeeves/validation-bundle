<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ApiIntegrationType extends Enum
{
    const NONE = 'none';
    const YARDI_VOYAGER = 'yardi voyager';
    const RESMAN = 'resman';
    const MRI = 'mri';
    const AMSI = 'amsi';

    public static $importMapping = [
        self::NONE          => 'csv',
        self::YARDI_VOYAGER => 'yardi',
        self::RESMAN        => 'resman',
        self::MRI           => 'mri',
        self::AMSI          => 'amsi',
    ];
}
