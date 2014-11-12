<?php

namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\BaseTestCase as Base;

class TenantListenerCase extends Base
{
    /**
     * @test
     */
    public function turnOnReporting()
    {
        $this->load(true);
        /**
         * @var $em EntityManagerr
         */
        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        /**
         * @var $tenant Tenant
         */
        $tenant = $em->getRepository("RjDataBundle:Tenant")->findOneBy(array('email' => 'ivan@rentrack.com'));
        $contracts = $tenant->getContracts();
        $this->assertNotEmpty($contracts);
        $tenant->setCreatedAt(new \DateTime());
        $em->flush($tenant);
        /**
         * @var Contract $contract
         */
        foreach ($contracts as $contract) {
            $this->assertNull($contract->getTransUnionStartAt());
            $this->assertFalse($contract->getReportToTransUnion());
        }

        $tenant->setIsVerified(UserIsVerified::PASSED);
        $em->flush($tenant);
        $today = new \DateTime();
        $contracts = $tenant->getContracts();
        /**
         * @var Contract $contract
         */
        foreach ($contracts as $contract) {
            $this->assertNotNull($contract->getTransUnionStartAt());
            $this->assertTrue($contract->getReportToTransUnion());
            $this->assertTrue(($contract->getTransUnionStartAt()->format('Ymd') === $today->format('Ymd')));
        }
    }
}
