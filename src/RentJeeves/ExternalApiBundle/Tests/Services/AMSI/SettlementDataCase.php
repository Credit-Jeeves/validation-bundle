<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\AMSI;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Entity\OrderRepository;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Services\AMSI\SettlementData;
use RentJeeves\TestBundle\Functional\BaseTestCase as Base;

class SettlementDataCase extends Base
{
    /** @var Holding */
    protected $holding;

    public function setUp()
    {
        $this->load(true);
        $this->holding = $this->getEntityManager()->getRepository('DataBundle:Holding')
            ->findOneByName('Rent Holding');
        $this->holding->setAccountingSystem(AccountingSystem::AMSI);
        $this->getEntityManager()->flush($this->holding);
    }

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

    public function batchIdDatesDataProvider()
    {
        return [
            [new \DateTime('-20 days'), 1, [
                [
                    'batchId' => 111555,
                    'amount' => '1500.00',
                    'groupId' => '24',
                ],
            ]],
            [new \DateTime('-340 days'), 2, [
                [
                    'batchId' => 325114,
                    'amount' => '1500.00',
                    'groupId' => '25'
                ],
            ]],
        ];
    }

    /**
     * @test
     * @dataProvider batchIdDatesDataProvider
     *
     * @param \DateTime $date
     * @param int       $countBatches
     * @param array     $expectedResult
     */
    public function shouldReturnBatchIdDataByDate(\DateTime $date, $countBatches, $expectedResult)
    {
        $result = $this->getAMSISettlementService()->getBatchesToClose($date, $this->holding);
        $this->assertCount($countBatches, $result);
        $this->assertEquals($expectedResult[0]['batchId'], $result[0]['batchId']);
        $this->assertEquals($expectedResult[0]['amount'], $result[0]['amount']);
        $this->assertEquals($expectedResult[0]['groupId'], $result[0]['groupId']);
    }

    /**
     * @test
     */
    public function shouldReturnCorrectData()
    {
        // Let's set all orders created_at to now. Thus we can get more test data.
        $em = $this->getEntityManager();
        $orders = $this->getOrderRepository()->findAll();
        $today = new \DateTime('now');
        /** @var Order $order */
        foreach ($orders as $order) {
            $order->setCreatedAt($today);
        }
        $em->flush();

        $expected = [
            ['batchId' => 111555, 'amount' => '3000.00', 'groupId' => '24'],
            ['batchId' => 125478, 'amount' => '1500.00', 'groupId' => '24'],
            ['batchId' => 325114, 'amount' => '1500.00', 'groupId' => '25'],
            ['batchId' => 325691, 'amount' => '3000.00', 'groupId' => '25'],
            ['batchId' => 325692, 'amount' => '1500.00', 'groupId' => '25'],
            ['batchId' => 325693, 'amount' => '3000.00', 'groupId' => '25'],
            ['batchId' => 325694, 'amount' => '1500.00', 'groupId' => '25'],
            ['batchId' => 325696, 'amount' => '3000.00', 'groupId' => '25'],
            ['batchId' => 325698, 'amount' => '6000.00', 'groupId' => '24'],
            ['batchId' => 555000, 'amount' => '2500.00', 'groupId' => '24'],
            ['batchId' => 555001, 'amount' => '2500.00', 'groupId' => '24'],
            ['batchId' => 555002, 'amount' => '2500.00', 'groupId' => '24'],
            ['batchId' => 555003, 'amount' => '1250.00', 'groupId' => '24']
        ];
        $result = $this->getAMSISettlementService()->getBatchesToClose($today, $this->holding);
        $this->assertCount(13, $result);
        // As SettlementData service returns datetime objects for dates and we have problems with timezones there,
        // we have to check each row separately. Let's check them selectively.
        $this->assertEquals(
            [$expected[0]['batchId'], $expected[0]['amount'], $expected[0]['groupId']],
            [$result[0]['batchId'], $result[0]['amount'], $result[0]['groupId']]
        );
        $this->assertEquals(
            [$expected[1]['batchId'], $expected[1]['amount'], $expected[1]['groupId']],
            [$result[1]['batchId'], $result[1]['amount'], $result[1]['groupId']]
        );
        $this->assertEquals(
            [$expected[5]['batchId'], $expected[5]['amount'], $expected[5]['groupId']],
            [$result[5]['batchId'], $result[5]['amount'], $result[5]['groupId']]
        );
        $this->assertEquals(
            [$expected[7]['batchId'], $expected[7]['amount'], $expected[7]['groupId']],
            [$result[7]['batchId'], $result[7]['amount'], $result[7]['groupId']]
        );
    }

    /**
     * @return SettlementData
     */
    protected function getAMSISettlementService()
    {
        return $this->getContainer()->get('accounting.amsi_settlement');
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Order');
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }
}
