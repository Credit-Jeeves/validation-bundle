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
    protected function getContract(DateTime $startAt = null, DateTime $finishAt = null)
    {
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        if (empty($startAt) || empty($finishAt)) {
            return $em->getRepository('RjDataBundle:Contract')->findOneBy(
                array(
                    'rent'    => 999999.99,
                    'balance' => 9999.89
                )
            );
        }
        $contract = new Contract();
        $contract->setRent(999999.99);
        $contract->setBalance(9999.89);
        $contract->setStartAt($startAt);
        $contract->setFinishAt($finishAt);

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
     * We test updated startAt on the table rj_contract when user create first order
     *
     * @test
     */
    public function updateStartAtOfContract()
    {
        $this->load(true);
        $startAt = new DateTime();
        $startAt->modify('-5 month');
        $finishAt = new DateTime();
        $finishAt->modify('+24 month');
        /**
         * @var $contract Contract
         */
        $contract = $this->getContract($startAt, $finishAt);
        $operations = $contract->getOperations();
        $this->assertTrue(($operations->count() === 0));
        $this->assertTrue(($contract->getStartAt() === $startAt));

        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $order = new Order();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setType(OrderType::AUTHORIZE_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($contract->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor = new DateTime();
        $operation->setPaidFor($paidFor);
        $operation->setOrder($order);

        $em->persist($operation);
        $em->persist($order);
        $em->flush();
        $em->refresh($contract);
        $this->assertEquals($paidFor->format('Ymd'), $contract->getStartAt()->format('Ymd'));
    }

    /**
     * We test do not update startAt on the table rj_contract when user create second order
     *
     * @depends updateStartAtOfContract
     * @test
     */
    public function doNotUpdateStartAtOfContract()
    {
        /**
         * @var $contract Contract
         */
        $contract = $this->getContract();
        $paidFor = new DateTime();
        $order = new Order();
        $order->setUser($contract->getTenant());
        $order->setSum(500);
        $order->setType(OrderType::AUTHORIZE_CARD);
        $order->setStatus(OrderStatus::COMPLETE);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setAmount(500);
        $operation->setGroup($contract->getGroup());
        $operation->setType(OperationType::RENT);
        $paidFor2 = new DateTime();
        $paidFor2->modify('+1 month');
        $operation->setPaidFor($paidFor2);
        $operation->setOrder($order);
        /**
         * @var $em EntityManager
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->persist($operation);
        $em->persist($order);
        $em->flush();
        $em->refresh($contract);

        $this->assertEquals($paidFor->format('Ymd'), $contract->getStartAt()->format('Ymd'));
    }
}
