<?php

namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\DataBundle\Tests\Traits\ContractAvailableTrait;
use RentJeeves\TestBundle\Functional\BaseTestCase;

class TransactionListenerCase extends BaseTestCase
{
    use ContractAvailableTrait;

    /**
     * @test
     */
    public function shouldAddJobToDbIfCreateReversedTransaction()
    {
        $this->load(true);

        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(2, $jobs);

        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        $contract = $this->getContract($startAt, $finishAt);
        $contract->getHolding()->setApiIntegrationType(ApiIntegrationType::AMSI);

        $order = new Order();
        $order->setUser($contract->getTenant());
        $order->setType(OrderType::HEARTLAND_CARD);
        $order->setStatus(OrderStatus::CANCELLED);
        $order->setSum(123);
        $order->setFee(0);
        $order->setPaymentProcessor(PaymentProcessor::HEARTLAND);

        $operation = new Operation();
        $operation->setOrder($order);
        $operation->setContract($contract);
        $operation->setPaidFor(new \DateTime());

        $this->getEntityManager()->persist($operation);
        $this->getEntityManager()->flush($operation);

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush($order);

        $transaction = new Heartland();
        $transaction->setStatus(TransactionStatus::REVERSED);
        $transaction->setIsSuccessful(true);
        $transaction->setAmount(123);
        $transaction->setOrder($order);

        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush($transaction);

        $jobs = $this->getEntityManager()->getRepository('RjDataBundle:Job')->findAll();
        $this->assertCount(3, $jobs);
        /** @var Job $job */
        $job = end($jobs);

        $this->assertEquals('api:accounting:amsi:return-payment', $job->getCommand());
    }
}
