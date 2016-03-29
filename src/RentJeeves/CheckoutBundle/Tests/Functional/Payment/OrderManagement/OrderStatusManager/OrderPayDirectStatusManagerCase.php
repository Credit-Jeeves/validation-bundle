<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\OrderPayDirect;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderPayDirectStatusManager;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class OrderPayDirectStatusManagerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSendEmailWhenSetOrderStatusToComplete()
    {
        $this->load(true);

        /** @var  $orderStatusManager OrderPayDirectStatusManager */
        $orderStatusManager = $this->getContainer()->get('payment_processor.order_status_manager.pay_direct');

        /** @var $order OrderPayDirect */
        $order = $this->getEntityManager()->getRepository('DataBundle:OrderPayDirect')->find(2);

        $this->assertNotNull($order, 'Check fixtures, OrderPayDirect #2 should exist');

        $order->setStatus(OrderStatus::NEWONE);
        $this->getEntityManager()->flush($order);

        $emailPlugin = $this->registerEmailListener();
        $emailPlugin->clean();

        $orderStatusManager->setComplete($order);

        $this->assertCount(1, $emailPlugin->getPreSendMessages(), '1 email is expected to be sent');
        $this->assertEquals('Your Rent is Paid!', $emailPlugin->getPreSendMessage(0)->getSubject());

        $emailPlugin->clean();
    }
}
