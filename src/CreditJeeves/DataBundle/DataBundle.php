<?php
namespace CreditJeeves\DataBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DataBundle extends Bundle
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

            Type::addType('ReportType', 'CreditJeeves\DataBundle\Enum\ReportType');
            Type::addType('UserIsVerified', 'CreditJeeves\DataBundle\Enum\UserIsVerified');
            Type::addType('UserType', 'CreditJeeves\DataBundle\Enum\UserType');
            Type::addType('UserCulture', 'CreditJeeves\DataBundle\Enum\UserCulture');
            Type::addType('OrderStatus', 'CreditJeeves\DataBundle\Enum\OrderStatus');
            Type::addType('OperationType', 'CreditJeeves\DataBundle\Enum\OperationType');
            Type::addType('LeadStatus', 'CreditJeeves\DataBundle\Enum\LeadStatus');
            Type::addType('GroupType', 'CreditJeeves\DataBundle\Enum\GroupType');
            Type::addType('GroupFeeType', 'CreditJeeves\DataBundle\Enum\GroupFeeType');
            Type::addType('LeadSource', 'CreditJeeves\DataBundle\Enum\LeadSource');
        }
    }
}
