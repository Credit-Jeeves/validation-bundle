<?php

namespace RentJeeves\CheckoutBundle\Tests\Services;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\BaseTestCase;

class PaidForCase extends BaseTestCase
{
    /**
     * @test
     */
    public function makeDatesFromDate()
    {
        $paidFor = $this->getMock('RentJeeves\CheckoutBundle\Services\PaidFor', array('getNow'), array(), '', false);
        $paidFor->expects($this->once())
            ->method('getNow')
            ->will($this->returnValue(new DateTime('2014-05-05')));
        $dateTime = new DateTime('2014-02-10');
        $paidTo = clone $dateTime;
        $this->assertEquals(
            $paidFor->createItem($dateTime) +
            $paidFor->createItem($dateTime->modify('+1 month')) +
            $paidFor->createItem($dateTime->modify('+1 month')) +
            $paidFor->createItem($dateTime->modify('+1 month')),
            $this->callNoPublicMethod($paidFor, 'makeDatesFromDate', array($paidTo))
        );
    }

    /**
     * @test
     */
    public function getArray()
    {
        $paidTo = new DateTime('2014-02-28');
        $now = clone $paidTo;
        $now->modify('+2 days');
        $paidTo->setTime(0, 0, 0);
        $paidTo->modify('-2 months');
        $paidForDate = clone $paidTo;
        $startAt = clone $paidTo;
        $dateTime = clone $paidTo;
        $contract = new Contract();
        $contract->setStartAt($startAt->modify('-1 month'));
        $contract->setPaidTo($paidTo);
        $contract->setRent(1000);
        $contract->setDueDate(28);

        $order = new OrderSubmerchant();
        $order->setStatus(OrderStatus::COMPLETE);

        $operation1 = new Operation();
        $operation1->setAmount(500);
        $operation1->setType(OperationType::RENT);
        $operation1->setPaidFor(clone $paidForDate->modify('+1 month'));
        $operation1->setOrder(clone $order);
        $operation1->setContract($contract);

        $operation2 = new Operation();
        $operation2->setAmount(500);
        $operation2->setType(OperationType::RENT);
        $operation2->setPaidFor(clone $paidForDate);
        $operation2->setOrder(clone $order);
        $operation2->setContract($contract);

        $operation3 = new Operation();
        $operation3->setAmount(500);
        $operation3->setType(OperationType::RENT);
        $operation3->setPaidFor(clone $paidForDate->modify('+1 month'));
        $operation3->setOrder(clone $order);
        $operation3->setContract($contract);

        $operation4 = new Operation();
        $operation4->setAmount(500);
        $operation4->setType(OperationType::RENT);
        $operation4->setPaidFor(clone $paidForDate->modify('+1 month'));
        $operation4->setOrder(clone $order);
        $operation4->setContract($contract);


        $contract->addOperation($operation1);
        $contract->addOperation($operation2);
        $contract->addOperation($operation3);
//        $contract->addOperation($operation4);


        $paidFor = $this->getMock('RentJeeves\CheckoutBundle\Services\PaidFor', array('getNow'), array(), '', false);
        $paidFor->expects($this->exactly(2))
            ->method('getNow')
            ->will($this->returnValue($now));

        $dateTime->setDate(null, null, 28);
        $this->assertEquals(
            $paidFor->createItem($dateTime) +
            $paidFor->createItem($dateTime->modify('+2 month')) +
            $paidFor->createItem($dateTime->modify('+1 month')) +
            $paidFor->createItem($dateTime->modify('+1 month')),
            $paidFor->getArray($contract)
        );
    }

    /**
     * @test
     */
    public function getArrayDefault()
    {
        $date = new DateTime();
        $contract = new Contract();
        $contract->setRent(1000);
        $contract->setDueDate(10);

        $paidFor = $this->getMock('RentJeeves\CheckoutBundle\Services\PaidFor', array('getNow'), array(), '', false);
        $paidFor->expects($this->any())
            ->method('getNow')
            ->will($this->returnValue(new DateTime()));


        $date->setDate(null, null, 10);
        $this->assertEquals(
            $paidFor->createItem($date) +
            $paidFor->createItem($date->modify('+1 month')),
            $paidFor->getArray($contract)
        );
    }

    /**
     * @test
     */
    public function getNowArrayDefault()
    {
        $date = new DateTime();
        $paidTo = clone $date;
        $startAt = clone $paidTo;
        $paidForDate = clone $paidTo;
        $paidTo->modify('+2 day');
        $paidTo->setTime(0, 0, 0);
        $contract = new Contract();
        $contract->setStartAt($startAt->modify('-1 month '));
        $contract->setPaidTo($paidTo);
        $contract->setRent(1000);
        $order = new OrderSubmerchant();
        $order->setStatus(OrderStatus::COMPLETE);
        $operation1 = new Operation();
        $operation1->setAmount(500);
        $operation1->setType(OperationType::RENT);
        $operation1->setPaidFor(clone $paidForDate);
        $operation1->setOrder(clone $order);
        $operation1->setContract($contract);
        $contract->addOperation($operation1);
        $paidFor = $this->getMock('RentJeeves\CheckoutBundle\Services\PaidFor', array('getNow'), array(), '', false);
        $paidFor->expects($this->any())
            ->method('getNow')
            ->will($this->returnValue(new DateTime()));
        $this->assertEquals(
            $paidFor->createItem($date) +
            $paidFor->createItem($date->modify('+1 month')),
            $paidFor->getArray($contract)
        );
    }
}
