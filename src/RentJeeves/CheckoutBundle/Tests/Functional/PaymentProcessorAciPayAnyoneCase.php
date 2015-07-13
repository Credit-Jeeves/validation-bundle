<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\OutboundTransactionStatus;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\BaseTestCase;

class PaymentProcessorAciPayAnyoneCase extends BaseTestCase
{
    /**
     * @return Order $order
     */
    protected function prepareOrder()
    {
        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(23);

        $this->assertNotEmpty($contract, 'Please check fixtures');

        $order = new Order();
        $order->setUser($contract->getTenant());
        $order->setStatus(OrderStatus::PENDING);
        $order->setType(OrderType::HEARTLAND_BANK);
        $order->setSum(600);
        $order->setPaymentProcessor(PaymentProcessor::ACI);
        $order->setDescriptor('Test Check');

        $operation = new Operation();
        $operation->setAmount(600);
        $operation->setType(OperationType::RENT);
        $operation->setOrder($order);
        $operation->setGroup($contract->getGroup());
        $operation->setContract($contract);
        $operation->setPaidFor(new \DateTime());

        $order->addOperation($operation);

        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();

        return $order;
    }

    /**
     * @test
     */
    public function executeOrder()
    {
        $this->load(true);

        $order = $this->prepareOrder();

        $orderStatus = $this->getContainer()->get('payment_processor.aci_pay_anyone')->executeOrder($order);

        $this->getEntityManager()->refresh($order);

        $this->assertNotEmpty($order->getDepositOutboundTransaction(), 'Failed creation outbound transaction');

        $this->assertEquals(
            OutboundTransactionStatus::SUCCESS,
            $order->getDepositOutboundTransaction()->getStatus(),
            'Order execution failed: ' . $order->getDepositOutboundTransaction()->getMessage()
        );

        $this->assertEquals(
            OrderStatus::SENDING,
            $order->getStatus(),
            sprintf('Invalid status order "%s" instead "%s"', $order->getStatus(), OrderStatus::SENDING)
        );

        $this->assertEquals(
            OrderStatus::SENDING,
            $orderStatus
        );

        return $order->getId();
    }

    /**
     * @param int $orderId
     *
     * @test
     * @depends executeOrder
     */
    public function cancelOrder($orderId)
    {
        /** @var Order $order */
        $order = $this->getEntityManager()->getRepository('DataBundle:Order')->find($orderId);

        $result = $this->getContainer()->get('payment_processor.aci_pay_anyone')->cancelOrder($order);

        $this->getEntityManager()->refresh($order);

        $this->assertTrue($result, 'Cancel order failed: ' . $order->getDepositOutboundTransaction()->getMessage());

        $this->assertEquals(
            OutboundTransactionStatus::CANCELLED,
            $order->getDepositOutboundTransaction()->getStatus(),
            sprintf(
                'Invalid status transaction "%s" instead  "%s"',
                $order->getDepositOutboundTransaction()->getStatus(),
                OutboundTransactionStatus::CANCELLED
            )
        );

        $this->assertEquals(
            OrderStatus::ERROR,
            $order->getStatus(),
            sprintf('Invalid status order "%s" instead "%s"', $order->getStatus(), OrderStatus::ERROR)
        );
    }
}
