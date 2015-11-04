<?php

namespace RentJeeves\LandlordBundle\Tests\Unit\Services;

use Doctrine\ORM\EntityManager;
use RentJeeves\LandlordBundle\Services\BatchDepositsManager;
use RentJeeves\TestBundle\BaseTestCase;

class BatchDepositsManagerCase extends BaseTestCase
{
    /** @var EntityManager $this->em */
    protected $em;

    /** @var  BatchDepositsManager */
    protected $depositManager;

    public function setUp()
    {
        parent::setUp();

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->depositManager = new BatchDepositsManager($this->em);
        $this->load(true);
    }

    /**
     * @test
     */
    public function shouldGetCountOfDepositsForGroupWithoutFilter()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(
            5,
            $this->depositManager->getCountDeposits($group, '', ''),
            'Group #24 should have 5 deposits'
        );
    }

    /**
     * @test
     */
    public function shouldGetCountOfDepositsForGroupAndFilterByBatch()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(
            1,
            $this->depositManager->getCountDeposits($group, 'batchId', '555000'),
            'Group #24 should have 1 deposit when filtering by batchId=555000'
        );
    }

    /**
     * @test
     */
    public function shouldGetCountOfDepositsForGroupAndFilterByTransaction()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(
            1,
            $this->depositManager->getCountDeposits($group, 'transactionId', '456456'),
            'Group #24 should have 1 deposits when filtering by transaction=456456'
        );
    }

    /**
     * @test
     */
    public function shouldGetZeroOfDepositsForGroupAndUnknownFilter()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(
            0,
            $this->depositManager->getCountDeposits($group, 'batchId', '0000'),
            'Group #24 should have 0 deposits for unknown batchId'
        );

        $this->assertEquals(
            0,
            $this->depositManager->getCountDeposits($group, 'transactionId', '0000'),
            'Group #24 should have 0 deposits for unknown transactionId'
        );
    }

    /**
     * @test
     */
    public function shouldGetZeroOfDepositsForGroupWithNoDeposits()
    {
        $group = $this->em->find('DataBundle:Group', 20);

        $this->assertEquals(
            0,
            $this->depositManager->getCountDeposits($group, '', ''),
            'Group #20 should have 0 deposits'
        );
    }

    /**
     * @test
     */
    public function shouldGetDepositsForGroupWithDeposits()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $deposits = $this->depositManager->getDeposits($group, '', '');
        $this->assertCount(5, $deposits, 'Expected 5 deposits for Group #24');

        $this->assertArrayHasKey('batchNumber', $deposits[0], 'BatchNumber not found in deposit');
        $this->assertEquals('325698', $deposits[0]['batchNumber'], 'Unexpected batchNumber');
        $this->assertArrayHasKey('depositDate', $deposits[0], 'DepositDate not found in deposit');
        $this->assertArrayHasKey('depositType', $deposits[0], 'DepositType not found in deposit');
        $this->assertEquals('Rent', $deposits[0]['depositType'], 'Unexpected depositType');
        $this->assertArrayHasKey('orderAmount', $deposits[0], 'OrderAmount not found in deposit');
        $this->assertEquals(1800, $deposits[0]['orderAmount'], 'Unexpected orderAmount');
        $this->assertArrayHasKey('orders', $deposits[0], 'Orders not found in deposit');
        $this->assertCount(2, $deposits[0]['orders'], 'Expected 2 orders in first deposit');
    }

    /**
     * @test
     */
    public function shouldGetDepositsWithPagination()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $deposits = $this->depositManager->getDeposits($group, '', '', 1, 2);
        $this->assertCount(2, $deposits, 'Expected 2 deposits for first page');

        $deposits = $this->depositManager->getDeposits($group, '', '', 3, 2);
        $this->assertCount(1, $deposits, 'Expected 41 deposits for third page');
    }
}
