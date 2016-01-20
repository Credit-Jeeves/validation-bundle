<?php
namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractHistory;
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
        $contract->setPaymentAllowed(false);

        $em->persist($contract);
        $em->flush($contract);
        /** @var ContractHistory[] $contractHistories */
        $contractHistories = $em->getRepository('RjDataBundle:ContractHistory')->findByObjectId($contract->getId());
        $this->assertNotNull($contractHistories, 'Should be created ContractHistory record after Contract');
        $this->assertCount(1, $contractHistories, 'Should be created just one ContractHistory record after Contract');
        $contractHistory = end($contractHistories);
        $this->assertEquals(
            false,
            $contractHistory->isPaymentAllowed(),
            'Should be added paymentAllowed to contract history on create'
        );
        //Update
        $contract->setRent(1100);
        $contract->setPaymentAccepted(PaymentAccepted::CASH_EQUIVALENT);
        $contract->setPaymentAllowed(true);
        $em->persist($contract);
        $em->flush($contract);

        $contractHistories = $em->getRepository('RjDataBundle:ContractHistory')->findByObjectId($contract->getId());
        $this->assertNotNull($contractHistories, 'We should have objects in DB');
        $this->assertCount(2, $contractHistories, 'We should have 2 objects in DB');
        $contractHistory = end($contractHistories);
        $this->assertEquals(
            PaymentAccepted::CASH_EQUIVALENT,
            $contractHistory->getPaymentAccepted(),
            'PaymentAccepted should be saved in history correctly'
        );
        $this->assertEquals(
            true,
            $contractHistory->isPaymentAllowed(),
            'Should be added paymentAllowed to contract history on update'
        );
    }
}
