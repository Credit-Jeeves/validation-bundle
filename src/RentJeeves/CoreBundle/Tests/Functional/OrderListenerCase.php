<?php

namespace RentJeeves\CoreBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class OrderListenerCase extends BaseTestCase
{
    /**
     * @test
     * FIXME fails on 2014-06-11 but not fails on 2014-06-09
     */
    public function shouldUnshiftContractDateWhenOrderIsCancelled()
    {
        $this->load(true);
        $container = static::getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.entity_manager');

        $orders = $em->getRepository('DataBundle:Order')
            ->findBy(
                array(
                    'status' => OrderStatus::COMPLETE,
                    'type' => 'heartland_card'
                )
            );

        /** @var Order $order */
        $order = $orders[0];

        /** @var Operation $operation */
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
