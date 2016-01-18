<?php
namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
use RentJeeves\TestBundle\BaseTestCase;
use DateTime;

class LoggableListenerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function createAndUpdate()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByCode('DXC6KXOAGX');
        /** @var Contract $contract */
        $contract = new Contract();
        $contract->setTenant($tenant);
        $contract->setRent(1000);
        $contract->setFinishAt(new DateTime());
        $contract->setStartAt(new DateTime());
        $contract->setStatus(ContractStatus::INVITE);
        $contract->setGroup($group);
        $contract->setDueDate($group->getGroupSettings()->getDueDate());
        $contract->setProperty($group->getGroupProperties()->last());
        $contract->setUnit($contract->getProperty()->getUnits()->first());
        $contract->setPaymentAccepted(PaymentAccepted::ANY);

        $em->persist($contract);
        $em->flush($contract);

        $contractHistory = $em->getRepository('RjDataBundle:ContractHistory')->findByObjectId($contract->getId());
        $this->assertNotNull($contractHistory);
        $this->assertCount(1, $contractHistory);

        //Update
        $contract->setRent(1100);
        $contract->setPaymentAccepted(PaymentAccepted::CASH_EQUIVALENT);
        $em->persist($contract);
        $em->flush($contract);

        $contractsHistory = $em->getRepository('RjDataBundle:ContractHistory')->findByObjectId($contract->getId());
        $this->assertNotNull($contractsHistory, 'We should have objects in DB');
        $this->assertCount(2, $contractsHistory, 'We should have 2 objects in DB');
        $contractHistory = end($contractsHistory);
        $this->assertEquals(
            PaymentAccepted::CASH_EQUIVALENT,
            $contractHistory->getPaymentAccepted(),
            'PaymentAccepted should be saved in history correctly'
        );
    }

    /**
     * @test
     */
    public function shouldCheckWhenFieldNotVersionedWeNotAddNewLogEntry()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('tenant11@example.com');
        /** @var Group $group */
        $group = $em->getRepository('DataBundle:Group')->findOneByCode('DXC6KXOAGX');
        /** @var Contract $contract */
        $contract = new Contract();
        $contract->setTenant($tenant);
        $contract->setRent(1000.00);
        $today = new \DateTime();
        $contract->setFinishAt($today);
        $contract->setStartAt($today);
        $contract->setStatus(ContractStatus::INVITE);
        $contract->setGroup($group);
        $contract->setDueDate($group->getGroupSettings()->getDueDate());
        $contract->setProperty($group->getGroupProperties()->last());
        $contract->setUnit($contract->getProperty()->getUnits()->first());
        $contract->setPaymentAccepted(PaymentAccepted::ANY);

        $em->persist($contract);
        $em->flush($contract);

        $contractHistory = $em->getRepository('RjDataBundle:ContractHistory')->findByObjectId($contract->getId());
        $this->assertNotNull($contractHistory);
        $this->assertCount(1, $contractHistory);

        $holding = $this->getEntityManager()->getRepository('DataBundle:Holding')->findOneByName('Estate Holding');
        $this->assertNotEmpty($holding, 'Holding should exist in fixtures');
        $contract->setHolding($holding);
        $contract->setRent('1000');
        $timezone = new \DateTimeZone('Arctic/Longyearbyen');
        $todayWithTimeZone = \DateTime::createFromFormat(\DateTime::ISO8601, $today->format(\DateTime::ISO8601));
        $todayWithTimeZone->setTimezone($timezone);
        $contract->setStartAt($todayWithTimeZone);

        $this->getEntityManager()->flush();
        $contractsHistory = $this->getEntityManager()->getRepository('RjDataBundle:ContractHistory')->findByObjectId(
            $contract->getId()
        );
        $this->assertNotNull($contractsHistory, 'We should have objects in DB');
        $this->assertCount(1, $contractsHistory, 'We should have 1 objects in DB');
    }
}
