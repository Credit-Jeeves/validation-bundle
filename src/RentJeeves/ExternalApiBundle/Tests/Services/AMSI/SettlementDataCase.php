<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use RentJeeves\ExternalApiBundle\Services\AMSI\SettlementData;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class SettlementDataCase extends Base
{
    /**
     * @test
     */
    public function couldBeGetAsService()
    {
        $amsiSettlementService = $this->getAMSISettlementService();

        $this->assertTrue(is_object($amsiSettlementService));
        $this->assertInstanceOf('RentJeeves\ExternalApiBundle\Services\AMSI\SettlementData', $amsiSettlementService);
    }

    /**
     * @return array
     */
    public function datesForGetSettlementDateProvider()
    {
        return [
            [null, new \DateTime(), new \DateTime()],
            [null, new \DateTime('2014-12-22'), new \DateTime('2014-12-22')],
            [null, new \DateTime('2014-12-30'), new \DateTime('2014-12-30')],
            [new \DateTime('2014-12-22'), null, new \DateTime('2014-12-25')],
            [new \DateTime('2014-12-24'), null, new \DateTime('2014-12-29')],
            [new \DateTime('2014-12-25'), null, new \DateTime('2014-12-30')],
            [new \DateTime('2015-04-15'), null, new \DateTime('2015-04-20')],
        ];
    }

    /**
     * @test
     * @dataProvider datesForGetSettlementDateProvider
     */
    public function shouldReturnCorrectFromGetSettlementDate($batchDate, $depositDate, $expectedDate)
    {
        $amsiSettlementService = $this->getAMSISettlementService();
        $result = $amsiSettlementService->getSettlementDate($batchDate, $depositDate);
        $this->assertEquals($expectedDate, $result);
    }

    /**
     * @return SettlementData
     */
    protected function getAMSISettlementService()
    {
        return $this->getContainer()->get('accounting.amsi_settlement');
    }
}
