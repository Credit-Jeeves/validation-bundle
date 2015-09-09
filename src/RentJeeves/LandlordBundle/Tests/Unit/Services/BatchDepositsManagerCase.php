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
        /**** TODO: Remove this when RT-1621 is merged. There depositAccounts are added to fixtures ****/
        $query = $this->em->createQuery('UPDATE DataBundle:Order o SET o.depositAccount = 1');
        $query->execute();
        /**** TODO: Remove this when RT-1621 is merged. There depositAccounts are added to fixtures ****/
    }

    /**
     * @test
     */
    public function shouldGetCountOfDepositsForGroupAndAllPaymentTypes()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(9, $this->depositManager->getCountDeposits($group, ''), 'Group #24 should have 9 deposits');
    }

    /**
     * @test
     */
    public function shouldGetCountOfDepositsForGroupAndBankPaymentType()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(
            0,
            $this->depositManager->getCountDeposits($group, 'bank'),
            'Group #24 should have 0 deposits for bank'
        );
    }

    /**
     * @test
     */
    public function shouldGetCountOfDepositsForGroupAndCardPaymentType()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(
            9,
            $this->depositManager->getCountDeposits($group, 'card'),
            'Group #24 should have 0 deposits for card'
        );
    }

    /**
     * @test
     */
    public function shouldGetZeroOfDepositsForGroupAndUnknownPaymentType()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $this->assertEquals(
            0,
            $this->depositManager->getCountDeposits($group, '00'),
            'Group #24 should have 0 deposits for 00'
        );
    }

    /**
     * @test
     */
    public function shouldGetZeroOfDepositsForGroupWithNoDeposits()
    {
        $group = $this->em->find('DataBundle:Group', 20);

        $this->assertEquals(0, $this->depositManager->getCountDeposits($group, ''), 'Group #20 should have 0 deposits');
    }

    /**
     * @test
     */
    public function shouldGetDepositsForGroupWithDeposits()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $deposits = $this->depositManager->getDeposits($group, '');
        $this->assertCount(9, $deposits, 'Expected 9 deposits for Group #24');

        $this->assertArrayHasKey('batchNumber', $deposits[0], 'BatchNumber not found in deposit');
        $this->assertEquals('111555', $deposits[0]['batchNumber'], 'Unexpected batchNumber');
        $this->assertArrayHasKey('depositDate', $deposits[0], 'DepositDate not found in deposit');
        $this->assertArrayHasKey('depositType', $deposits[0], 'DepositType not found in deposit');
        $this->assertEquals('rent', $deposits[0]['depositType'], 'Unexpected depositType');
        $this->assertArrayHasKey('orderAmount', $deposits[0], 'OrderAmount not found in deposit');
        $this->assertEquals(3000, $deposits[0]['orderAmount'], 'Unexpected orderAmount');
        $this->assertArrayHasKey('orders', $deposits[0], 'Orders not found in deposit');
        $this->assertCount(2, $deposits[0]['orders'], 'Expected 2 orders in first deposit');
    }

    /**
     * @test
     */
    public function shouldGetDepositsWithPagination()
    {
        $group = $this->em->find('DataBundle:Group', 24);

        $deposits = $this->depositManager->getDeposits($group, '', 1, 5);
        $this->assertCount(5, $deposits, 'Expected 5 deposits for first page');

        $deposits = $this->depositManager->getDeposits($group, '', 2, 5);
        $this->assertCount(4, $deposits, 'Expected 4 deposits for second page');
    }
}
