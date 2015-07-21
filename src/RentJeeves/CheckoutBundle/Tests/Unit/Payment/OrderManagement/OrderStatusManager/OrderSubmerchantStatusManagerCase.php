<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\Payment\OrderManagement\OrderStatusManager;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use RentJeeves\CheckoutBundle\Payment\OrderManagement\OrderStatusManager\OrderStatusManagerInterface;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\DataBundle\Tests\Traits\TransactionAvailableTrait;
use RentJeeves\TestBundle\BaseTestCase;

class OrderSubmerchantStatusManagerCase extends BaseTestCase
{
    use TransactionAvailableTrait;
    use ContractAvailableTrait;

    /**
     * @return OrderSubmerchant
     */
    protected function createOrder()
    {
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');

        $contract = $this->getContract($startAt, $finishAt);
        $operations = $contract->getOperations();
        $this->assertTrue(($operations->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        $order = new OrderSubmerchant();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setPaymentType(OrderPaymentType::CARD);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($contract->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);

        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->persist($order);

        return $order;
    }

    /**
     * @return OrderStatusManagerInterface
     */
    protected function getStatusManager()
    {
        $this->getContainer()->get('payment_processor.order_status_manager');
    }

    /**
     * @test
     * expectedException \LogicException
     */
    public function setReissued()
    {
        $this->getStatusManager()->setReissued($this->createOrder());
    }
}
