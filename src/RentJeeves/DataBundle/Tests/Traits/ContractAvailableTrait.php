<?php

namespace RentJeeves\DataBundle\Tests\Traits;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\CoreBundle\DateTime;

trait ContractAvailableTrait
{
    /**
     * @TODO: add contract as attribute
     *
     * @param  DateTime $startAt
     * @param  DateTime $finishAt
     * @return Contract
     */
    public function getContract(DateTime $startAt = null, DateTime $finishAt = null)
    {
        /** @var $em EntityManager */
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

        /** @var $tenant Tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')->findOneBy(
            [
                'email'  => 'tenant11@example.com'
            ]
        );

        $this->assertNotNull($tenant);
        $contract->setTenant($tenant);
        /** @var $unit Unit */
        $unit = $em->getRepository('RjDataBundle:Unit')->findOneBy(
            [
                'name'  => '1-a',
                'group' => $em->getRepository('DataBundle:Group')->find(24)
            ]
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
}
