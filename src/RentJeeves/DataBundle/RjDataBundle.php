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
            Type::addType('TransactionStatus', 'RentJeeves\DataBundle\Enum\TransactionStatus');
            Type::overrideType(Type::DATETIME, 'RentJeeves\DataBundle\DBAL\Types\DateTimeType');
            Type::overrideType(Type::DATE, 'RentJeeves\DataBundle\DBAL\Types\DateType');
            Type::addType('PaymentTypeACH', 'RentJeeves\DataBundle\Enum\PaymentTypeACH');
            Type::addType('PaymentTypeCC', 'RentJeeves\DataBundle\Enum\PaymentTypeCC');
            Type::addType('ExternalApi', 'RentJeeves\DataBundle\Enum\ExternalApi');
            Type::addType('PaymentAccepted', 'RentJeeves\DataBundle\Enum\PaymentAccepted');
            Type::addType('ApiIntegrationType', 'RentJeeves\DataBundle\Enum\ApiIntegrationType');
            Type::addType('PaymentBatchStatus', 'RentJeeves\DataBundle\Enum\PaymentBatchStatus');
            Type::addType('PaymentProcessor', 'RentJeeves\DataBundle\Enum\PaymentProcessor');
            Type::addType('ImportSource', 'RentJeeves\DataBundle\Enum\ImportSource');
            Type::addType('ImportType', 'RentJeeves\DataBundle\Enum\ImportType');
            Type::addType('SynchronizationStrategy', 'RentJeeves\DataBundle\Enum\SynchronizationStrategy');
            Type::addType('BankAccountType', 'RentJeeves\DataBundle\Enum\BankAccountType');
            Type::addType('OrderAlgorithmType', 'RentJeeves\DataBundle\Enum\OrderAlgorithmType');
            Type::addType('OutboundTransactionType', 'RentJeeves\DataBundle\Enum\OutboundTransactionType');
            Type::addType('OutboundTransactionStatus', 'RentJeeves\DataBundle\Enum\OutboundTransactionStatus');
            Type::addType('DepositAccountType', 'RentJeeves\DataBundle\Enum\DepositAccountType');
            Type::addType('YardiPostMonthOption', 'RentJeeves\DataBundle\Enum\YardiPostMonthOption');
            Type::addType('TypeDebitFee', 'RentJeeves\DataBundle\Enum\TypeDebitFee');
            Type::addType('YardiNsfPostMonthOption', 'RentJeeves\DataBundle\Enum\YardiNsfPostMonthOption');
            Type::addType('ImportModelType', 'RentJeeves\DataBundle\Enum\ImportModelType');
            Type::addType('ImportStatus', 'RentJeeves\DataBundle\Enum\ImportStatus');

            $databasePlatform = $this->container->get('doctrine')
                ->getManager()
                ->getConnection()
                ->getDatabasePlatform();
            $databasePlatform->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}
