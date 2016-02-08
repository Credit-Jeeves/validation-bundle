<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class AccountingSystem extends Enum
{
    const NONE = 'none';

    const AMSI = 'amsi';
    const MRI = 'mri';
    const MRI_BOSTONPOST = 'mri bostonpost';
    const PROMAS = 'promas';
    const RESMAN = 'resman';
    const YARDI_VOYAGER = 'yardi voyager';
    const YARDI_GENESIS = 'yardi genesis';
    const YARDI_GENESIS_2 = 'yardi genesis v2';

    public static $importMapping = [
        self::NONE          => 'csv',
        self::YARDI_VOYAGER => 'yardi',
        self::RESMAN        => 'resman',
        self::MRI           => 'mri',
        self::AMSI          => 'amsi',
    ];

    public static $integratedWithApi = [
        self::YARDI_VOYAGER => 'yardi',
        self::RESMAN        => 'resman',
        self::MRI           => 'mri',
        self::AMSI          => 'amsi',
    ];
}
