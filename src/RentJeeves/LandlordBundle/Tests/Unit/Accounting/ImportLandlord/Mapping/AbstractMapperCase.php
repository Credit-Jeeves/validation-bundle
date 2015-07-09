<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Accounting\ImportLandlord\Mapping;

use RentJeeves\TestBundle\Command\BaseTestCase;

abstract class AbstractMapperCase extends BaseTestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
     */
    protected function getEmMock()
    {
        return $this->getMock(
            'Doctrine\ORM\EntityManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Monolog\Logger
     */
    protected function getLoggerMock()
    {
        return $this->getMock(
            'Monolog\Logger',
            [],
            [],
            '',
            false
        );
    }
}
