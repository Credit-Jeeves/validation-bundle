<?php

namespace CreditJeeves\DataBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DataBundle extends Bundle
{
    protected static $isLoaded = false;
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!static::$isLoaded) { // TODO find better way
            static::$isLoaded = true;
            Type::addType('encrypt', 'CreditJeeves\DataBundle\Type\Encrypt');
            Type::addType('ReportType', 'CreditJeeves\DataBundle\Enum\ReportType');
            Type::addType('UserIsVerified', 'CreditJeeves\DataBundle\Enum\UserIsVerified');
            Type::addType('UserType', 'CreditJeeves\DataBundle\Enum\UserType');
            Type::addType('UserCulture', 'CreditJeeves\DataBundle\Enum\UserCulture');
            Type::addType('AtbType', 'CreditJeeves\DataBundle\Enum\AtbType');
        }
    }
}
