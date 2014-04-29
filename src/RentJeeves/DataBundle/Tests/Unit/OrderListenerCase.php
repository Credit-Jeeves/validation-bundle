<?php

namespace RentJeeves\DataBundle\Tests\Unit;

use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\OrderType;
use DateTime;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TestBundle\BaseTestCase as Base;

class OrderListenerCase extends Base
{
    protected function getContract($startAt, $finishAt)
    {
        $contract = new Contract();
        $contract->setRent(1200);

        $contract->setStartAt($startAt);
        $contract->setFinishAt($finishAt);

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );

        $this->assertNotNull($tenant);
        $contract->setTenant($tenant);
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => '1-a'
            )
        );

        $this->assertNotNull($unit);

        $contract->setUnit($unit);
        $contract->setGroup($unit->getGroup());
        $contract->setHolding($unit->getHolding());
        $contract->setProperty($unit->getProperty());
        $contract->setStatus(ContractStatus::APPROVED);

        $em->persist($contract);
        $em->flush();

        return $contract;
    }

    /**
     * @test
     */
    public function testUpdateStartAtOfContract()
    {
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        $contract = $this->getContract($startAt, $finishAt);

        $operations = $contract->getOperations();
        $this->assertTrue(($operations->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));
        $contractId = $contract->getId();
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->clear();

        /**
         * @var $contract Contract
         */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => '1-a'
            )
        );
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );
        $order = new Order();
        $order->setUser($tenant);
        $order->setSum(500);
        $order->setType(OrderType::AUTHORIZE_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($unit->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);
        $order->addOperation($operation);

        $em->persist($operation);
        $em->persist($order);
        $em->flush();
        $em->clear();

        /**
         * @var $contract Contract
         */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);

        $this->assertEquals($paidFor->format('Ymd'), $contract->getStartAt()->format('Ymd'));

        /**
         * @var $contract Contract
         */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => '1-a'
            )
        );
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );
        $order = new Order();
        $order->setUser($tenant);
        $order->setSum(500);
        $order->setType(OrderType::AUTHORIZE_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($unit->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor2 = new DateTime();
        $paidFor2->modify('+1 month');
        $operation->setPaidFor($paidFor2);
        $operation->setOrder($order);
        $order->addOperation($operation);

        $em->persist($operation);
        $em->persist($order);
        $em->flush();
        $em->clear();

        /**
         * @var $contract Contract
         */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);

        $this->assertEquals($paidFor->format('Ymd'), $contract->getStartAt()->format('Ymd'));


        /**
         * @var $contract Contract
         */
        $contract = $this->getContract($startAt, $finishAt);
        $contractId = $contract->getId();
        $startAtContract = $contract->getStartAt();
        /**
         * @var $unit Unit
         */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            array(
                'name'  => '1-a'
            )
        );
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            array(
                'email'  => 'tenant11@example.com'
            )
        );
        $order = new Order();
        $order->setUser($tenant);
        $order->setSum(500);
        $order->setType(OrderType::AUTHORIZE_CARD);
        $order->setStatus(OrderStatus::PENDING);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($unit->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor3 = new DateTime();
        $paidFor3->modify('+2 month');
        $operation->setPaidFor($paidFor3);
        $operation->setOrder($order);
        $order->addOperation($operation);

        $em->persist($operation);
        $em->persist($order);
        $em->flush();
        $em->clear();

        /**
         * @var $contract Contract
         */
        $contract = $em->getRepository('RjDataBundle:Contract')->find($contractId);
        $this->assertEquals($startAt->format('Ymd'), $contract->getStartAt()->format('Ymd'));
    }
}
