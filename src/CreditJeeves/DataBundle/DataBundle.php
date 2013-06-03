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
            Type::addType('encryptobject', 'CreditJeeves\DataBundle\Type\EncryptObject');
            Type::addType('ReportType', 'CreditJeeves\DataBundle\Enum\ReportType');
            Type::addType('UserIsVerified', 'CreditJeeves\DataBundle\Enum\UserIsVerified');
            Type::addType('UserType', 'CreditJeeves\DataBundle\Enum\UserType');
            Type::addType('UserCulture', 'CreditJeeves\DataBundle\Enum\UserCulture');
            Type::addType('AtbType', 'CreditJeeves\DataBundle\Enum\AtbType');
            Type::addType('OrderStatus', 'CreditJeeves\DataBundle\Enum\OrderStatus');
            Type::addType('OperationType', 'CreditJeeves\DataBundle\Enum\OperationType');

            $this->container->get('doctrine.orm.default_entity_manager')
                ->getConnection()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}
