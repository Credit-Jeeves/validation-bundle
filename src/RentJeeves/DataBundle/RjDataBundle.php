<?php

namespace RentJeeves\DataBundle;

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
            $this->container->get('doctrine.orm.default_entity_manager')
                ->getConnection()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}
