<?php

namespace RentJeeves\CheckoutBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciPayAnyone;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\BaseTestCase;

class PaymentProcessorAciPayAnyoneCase extends BaseTestCase
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var PaymentProcessorAciPayAnyone
     */
    protected $paymentProcessor;

    public function setUp()
    {
        $this->load(true);

        /** @var Contract $contract */
        $contract = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->find(23);

        if ($contract) {
            $this->order = new Order();
            $this->order->setUser($contract->getTenant());
            $this->order->setStatus(OrderStatus::PENDING);
            $this->order->setType(OrderType::HEARTLAND_BANK);
            $this->order->setSum(600);
            $this->order->setPaymentProcessor(PaymentProcessor::ACI_PAY_ANYONE);
            $this->order->setDescriptor('Test Check');

            $operation = new Operation();
            $operation->setAmount(600);
            $operation->setType(OperationType::RENT);
            $operation->setOrder($this->order);
            $operation->setGroup($contract->getGroup());
            $operation->setContract($contract);
            $operation->setPaidFor(new \DateTime());

            $this->order->addOperation($operation);

            $this->getEntityManager()->persist($operation);
            $this->getEntityManager()->persist($this->order);
            $this->getEntityManager()->flush();
        }

//        $this->paymentProcessor = $this->getContainer()->get('payment_processor.aci_pay_anyone');
    }

    /**
     * @test
     */
    public function executeOrder()
    {
//        $this->assertInstanceOf('CreditJeeves\DataBundle\Entity\Order', $this->order);
//        $this->paymentProcessor->executeOrder($this->order);
    }
}
