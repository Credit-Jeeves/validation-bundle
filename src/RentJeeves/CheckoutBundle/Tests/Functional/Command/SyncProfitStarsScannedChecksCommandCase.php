<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional\Command;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\Command\SyncProfitStarsScannedChecksCommand;
use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Command\BaseTestCase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class SyncProfitStarsScannedChecksCommandCase extends BaseTestCase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Group with id#9999 not found
     */
    public function shouldTrowExceptionIfGroupNotFound()
    {
        $this->load(true);
        $command = new SyncProfitStarsScannedChecksCommand();

        $this->executeCommandTester(
            $command,
            [
                '--group-id' => 9999
            ]
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Date 1 March is incorrect
     */
    public function shouldTrowExceptionIfIncorrectDatePassed()
    {
        $command = new SyncProfitStarsScannedChecksCommand();

        $this->executeCommandTester(
            $command,
            [
                '--date' => '1 March'
            ]
        );
    }

    /**
     * @test
     */
    public function shouldCreateProfitStarsBatchesAndOrdersWhenTheyAreNew()
    {
        $this->load(true);
        $command = new SyncProfitStarsScannedChecksCommand();

        $em = $this->getEntityManager();
        /** @var Group $group */
        $group = $em->find('DataBundle:Group', 24);
        $this->assertNotNull($group, 'Group #24 should exist');

        $holding = $group->getHolding();
        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setHolding($holding);
        $profitStarsSettings->setMerchantId(223586);
        $holding->setProfitStarsSettings($profitStarsSettings);
        $em->persist($profitStarsSettings);
        $depositAccount = $em->find('RjDataBundle:DepositAccount', 1);
        $depositAccount->setMerchantName(1023318);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $em->persist($depositAccount);
        $em->flush();
        $countProfitStarsBatchesBeforeSync = count($em->getRepository('RjDataBundle:ProfitStarsBatch')->findAll());
        $countOrdersBeforeSync = count($em->getRepository('DataBundle:Order')->findAll());

        $plugin = $this->registerEmailListener();
        $plugin->clean();

        $this->executeCommandTester(
            $command,
            [
                '--group-id' => 24,
                '--date' => '2016-01-01',
            ]
        );

        $countProfitStarsBatchesAfterSync = count($em->getRepository('RjDataBundle:ProfitStarsBatch')->findAll());
        $this->assertEquals(
            $countProfitStarsBatchesBeforeSync + 3,
            $countProfitStarsBatchesAfterSync,
            '3 batches should be added'
        );

        $ordersAfterSync = $em->getRepository('DataBundle:Order')->findAll();
        $this->assertEquals($countOrdersBeforeSync + 2, count($ordersAfterSync), '2 new orders should be added');

        $this->assertCount(2, $plugin->getPreSendMessages(), '2 emails are expected to be send');
        $this->assertEquals('Rent Payment Receipt', $plugin->getPreSendMessage(0)->getSubject(), 'Unknown email1 sent');
        $this->assertEquals('Rent Payment Receipt', $plugin->getPreSendMessage(1)->getSubject(), 'Unknown email2 sent');

        /** @var Order $order */
        $order = $ordersAfterSync[0];
        $profitStarsTransactions = $em->getRepository('RjDataBundle:ProfitStarsTransaction')->findAll();
        $this->assertCount(2, $profitStarsTransactions, '2 ProfitStars transactions should be created');
        $this->assertNotNull($profitStarsTransactions[0]->getItemId(), 'ItemId should be set');
        $this->assertNotNull($profitStarsTransactions[1]->getItemId(), 'ItemId should be set');

        // execute command again and check that no new orders and batches created
        $this->executeCommandTester(
            $command,
            [
                '--group-id' => 24,
                '--date' => '2016-01-01',
            ]
        );

        $countProfitStarsBatchesAfterSync2 = count($em->getRepository('RjDataBundle:ProfitStarsBatch')->findAll());
        $this->assertEquals(
            $countProfitStarsBatchesAfterSync,
            $countProfitStarsBatchesAfterSync2,
            'No new batches should be added'
        );

        $countOrdersAfterSync2 = count($em->getRepository('DataBundle:Order')->findAll());
        $this->assertEquals(count($ordersAfterSync), $countOrdersAfterSync2, 'No new orders should be added');
    }
}
