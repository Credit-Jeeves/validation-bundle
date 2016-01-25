<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class AccountingSystem extends Enum
{
    const NONE = 'none';
    const YARDI_VOYAGER = 'yardi voyager';
    const RESMAN = 'resman';
    const MRI = 'mri';
    const AMSI = 'amsi';
    const MRI_BOSTON_POST = 'mri boston post';

    public static $importMapping = [
        self::NONE          => 'csv',
        self::YARDI_VOYAGER => 'yardi',
        self::RESMAN        => 'resman',
        self::MRI           => 'mri',
        self::AMSI          => 'amsi',
    ];
}
