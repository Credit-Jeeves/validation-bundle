<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class OrderListenerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldUnshiftContractDateWhenOrderIsCancelled()
    {
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $orders = $em->getRepository('DataBundle:Order')
            ->findBy(
                array(
                    'status' => OrderStatus::COMPLETE,
                    'type' => 'heartland_card'
                )
            );
        $order = $orders[0];

        $operation = $order->getOperations()->last();
        $contract = $operation->getContract();
        $currentPaidToDate = clone $contract->getPaidTo();
        $expectedPaidTo = $currentPaidToDate->format('Y-m-d');

        $order->setStatus(OrderStatus::CANCELLED);
        $em->flush();

        $newPaidToDate = $currentPaidToDate->modify('-25 days');
        $actualPaidTo = $newPaidToDate->format('Y-m-d');

        $this->assertNotEquals($expectedPaidTo, $actualPaidTo);
        $this->assertEquals($newPaidToDate, $contract->getPaidTo());
    }
} 
