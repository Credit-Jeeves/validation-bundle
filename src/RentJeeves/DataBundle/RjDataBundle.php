<?php

namespace RentJeeves\DataBundle;

use RentJeeves\DataBundle\DBAL\Types\DateTimeType;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;

class RjDataBundle extends Bundle
{
    /**
     * @var bool
     */
    protected static $isLoaded = false;
    
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!static::$isLoaded) { // TODO find better way
            static::$isLoaded = true;
            Type::addType('ContractStatus', 'RentJeeves\DataBundle\Enum\ContractStatus');
            Type::addType('PaymentAccountType', 'RentJeeves\DataBundle\Enum\PaymentAccountType');
            Type::addType('PaymentStatus', 'RentJeeves\DataBundle\Enum\PaymentStatus');
            Type::addType('PaymentType', 'RentJeeves\DataBundle\Enum\PaymentType');
            Type::addType('DepositAccountStatus', 'RentJeeves\DataBundle\Enum\DepositAccountStatus');
            Type::addType('DisputeCode', 'RentJeeves\DataBundle\Enum\DisputeCode');
            Type::overrideType(Type::DATETIME, 'RentJeeves\DataBundle\DBAL\Types\DateTimeType');
            Type::overrideType(Type::DATE, 'RentJeeves\DataBundle\DBAL\Types\DateType');

            $databasePlatform = $this->container->get('doctrine')
                ->getManager()
                ->getConnection()
                ->getDatabasePlatform();
            $databasePlatform->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}
