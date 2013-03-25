<?php

namespace CreditJeeves\DataBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DataBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Type::addType('ReportTypeEnum', 'CreditJeeves\DataBundle\Enum\ReportTypeEnum');
    }
}
