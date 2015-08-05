<?php
namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
        $contract->setBalance(1000);
        $contract->setFinishAt(new DateTime());
        $contract->setStartAt(new DateTime());
        $contract->setStatus(ContractStatus::INVITE);
        $contract->setGroup($group);
        $contract->setDueDate($group->getGroupSettings()->getDueDate());
        $contract->setProperty($group->getGroupProperties()->last());
        $contract->setUnit($contract->getProperty()->getUnits()->first());

        $em->persist($contract);
        $em->flush($contract);

        $contractHistory = $em->getRepository('RjDataBundle:ContractHistory')->findByObjectId($contract->getId());
        $this->assertNotNull($contractHistory);
        $this->assertCount(1, $contractHistory);

        //Update
        $contract->setRent(1100);
        $contract->setBalance(1100);
        $em->persist($contract);
        $em->flush($contract);

        $contractHistory = $em->getRepository('RjDataBundle:ContractHistory')->findByObjectId($contract->getId());
        $this->assertNotNull($contractHistory);
        $this->assertCount(2, $contractHistory);
    }
}
