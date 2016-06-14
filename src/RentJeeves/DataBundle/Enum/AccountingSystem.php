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
    const RENT_MANAGER = 'rent manager';

    public static $importMapping = [
        self::NONE          => 'csv',
        self::YARDI_VOYAGER => 'yardi',
        self::RESMAN        => 'resman',
        self::MRI           => 'mri',
        self::AMSI          => 'amsi',
        self::MRI_BOSTONPOST => 'csv',
        self::YARDI_GENESIS  => 'csv',
        self::YARDI_GENESIS_2 => 'csv',
        self::PROMAS         => 'csv',
    ];

    public static $integratedWithApi = [
        self::YARDI_VOYAGER => 'yardi',
        self::RESMAN        => 'resman',
        self::MRI           => 'mri',
        self::AMSI          => 'amsi',
        self::RENT_MANAGER  => 'rent manager',
    ];

    public static $integratedWithCsv = [
        self::NONE,
        self::MRI_BOSTONPOST,
        self::YARDI_GENESIS,
        self::YARDI_GENESIS_2,
        self::PROMAS,
    ];

    public static $paymentHostIntegrated = [
        self::RESMAN        => 'resman',
        self::MRI           => 'mri',
    ];

    public static $allowedEditLeaseId = [
        self::MRI_BOSTONPOST,
        self::AMSI,
        self::PROMAS
    ];
}
